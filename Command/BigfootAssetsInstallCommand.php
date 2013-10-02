<?php

namespace Bigfoot\Bundle\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Runs bigfoot:theme:install and assets:install
 */
class BigfootAssetsInstallCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('bigfoot:assets:install')
            ->setDefinition(array(
                new InputArgument('target', InputArgument::OPTIONAL, 'The target directory', 'web'),
            ))
            ->addOption('symlink', null, InputOption::VALUE_NONE, 'Symlinks the assets instead of copying it')
            ->addOption('relative', null, InputOption::VALUE_NONE, 'Make relative symlinks')
            ->setDescription('Installs assets for the configured bigfoot theme into target/admin then runs the sf2 assets:install command.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command runs the bigfoot:theme:install and assets:install commands, passing over the target, symlink, and relative options.
See those commands descriptions for more information.
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
        $target = $input->getArgument('target');
        $symlink = $input->getOption('symlink');
        $relative = $input->getOption('relative');
        $input = new ArrayInput(array(
            'target' => $target,
            '--symlink' => $symlink,
            '--relative' => $relative,
        ));

        $this->runCommand('bigfoot:theme:install', $input, $output);
        $this->runCommand('assets:install', $input, $output);
    }

    protected function runCommand($name, ArrayInput $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find($name);
        return $command->run($input, $output);
    }
}
