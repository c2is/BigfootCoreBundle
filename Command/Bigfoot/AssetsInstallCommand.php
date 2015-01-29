<?php

namespace Bigfoot\Bundle\CoreBundle\Command\Bigfoot;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

use Bigfoot\Bundle\CoreBundle\Command\BaseCommand;

/**
 * Runs bigfoot:theme:install and assets:install
 */
class AssetsInstallCommand extends BaseCommand
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
            ->setDescription('Deprecated : should not be used anymore. Will be removed in bigfoot 3.1')
            ->setHelp(<<<EOT
Deprecated : should not be used anymore. Will be removed in bigfoot 3.1
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

    }

    protected function runCommand($name, ArrayInput $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find($name);

        return $command->run($input, $output);
    }
}
