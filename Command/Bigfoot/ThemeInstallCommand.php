<?php

namespace Bigfoot\Bundle\CoreBundle\Command\Bigfoot;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

use Bigfoot\Bundle\CoreBundle\Command\BaseCommand;

/**
 * Command that places the active bigfoot theme web assets into a given directory.
 */
class ThemeInstallCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('bigfoot:theme:install')
            ->setDefinition(array(
                new InputArgument('target', InputArgument::OPTIONAL, 'The target directory', 'web'),
            ))
            ->addOption('symlink', null, InputOption::VALUE_NONE, 'Symlinks the assets instead of copying it')
            ->addOption('relative', null, InputOption::VALUE_NONE, 'Make relative symlinks')
            ->setDescription('Installs assets for the configured bigfoot theme into target/admin then runs the sf2 assets:install command.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command installs the configured bigfoot theme bundle assets into a given
directory (e.g. the web directory) then runs the "normal" assets:install command with the same target argument.

<info>php %command.full_name% web</info>

An "admin" directory will be created inside the target directory, and the
"Resources/assets" directory of each bundle will be copied into it.

To create a symlink instead of copying the assets, use the
<info>--symlink</info> option (it will be passed to assets:install aswell):

<info>php %command.full_name% web --symlink</info>

To make symlink relative, add the <info>--relative</info> option (it will be passed to assets:install aswell):

<info>php %command.full_name% web --symlink --relative</info>

EOT
            )
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the target directory does not exist or symlink cannot be used
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $targetArg = rtrim($input->getArgument('target'), '/');

        if (!is_dir($targetArg)) {
            throw new \InvalidArgumentException(sprintf('The target directory "%s" does not exist.', $input->getArgument('target')));
        }

        if (!function_exists('symlink') && $input->getOption('symlink')) {
            throw new \InvalidArgumentException('The symlink() function is not available on your system. You need to install the assets without the --symlink option.');
        }

        $filesystem    = $this->getContainer()->get('filesystem');
        $themeBundle   = $this->getContainer()->get('kernel')->getBundle($this->getContainer()->getParameter('bigfoot.theme.bundle'));
        $contentBundle = $this->getContainer()->get('kernel')->getBundle('BigfootContentBundle');
        $images        = $contentBundle->getPath().'/Resources/public/images';

        $this->recurseCopy($images, $targetArg.'/images');

        if (is_dir($originDir = $themeBundle->getPath().'/Resources/assets')) {
            $targetDir = $targetArg.'/admin';

            $output->writeln(sprintf('Installing bigfoot theme assets from <comment>%s</comment> into <comment>%s</comment>', $themeBundle->getNamespace(), $targetDir));

            $filesystem->remove($targetDir);

            if ($input->getOption('symlink')) {
                if ($input->getOption('relative')) {
                    $relativeOriginDir = $filesystem->makePathRelative($originDir, realpath($targetArg));
                } else {
                    $relativeOriginDir = $originDir;
                }

                $filesystem->symlink($relativeOriginDir, $targetDir);
            } else {
                $filesystem->mkdir($targetDir, 0777);

                // We use a custom iterator to ignore VCS files
                $filesystem->mirror($originDir, $targetDir, Finder::create()->in($originDir));
            }
        }
    }

    protected function recurseCopy($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);

        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ( $file != '..' )) {
                if (is_dir($src.'/'.$file)) {
                    $this->recurseCopy($src.'/'.$file, $dst.'/'.$file);
                } else {
                    copy($src.'/'.$file, $dst.'/'.$file);
                }
            }
        }

        closedir($dir);
    }
}
