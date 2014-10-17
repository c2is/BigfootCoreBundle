<?php

namespace Bigfoot\Bundle\CoreBundle\Command\Bigfoot\Labels;

use Bigfoot\Bundle\CoreBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;

class Import extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('bigfoot:labels:import')
            ->setDefinition(array(
                new InputArgument('file', InputArgument::OPTIONAL, 'The label dictionary file', 'app/Resources/translatable_label/dictionary.yml'),
            ))
            ->setDescription('Synchronizes labels stored in database with those found in the file.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command runs the bigfoot:theme:install and assets:install commands, passing over the target, symlink, and relative options.
See those commands descriptions for more information.
EOT
            )
        ;
    }
}
