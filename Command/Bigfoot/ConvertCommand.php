<?php

namespace Bigfoot\Bundle\CoreBundle\Command\Bigfoot;

use Bigfoot\Bundle\CoreBundle\Command\BaseCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class ConvertCommand extends BaseCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('bigfoot:labels:convert')
            ->setDescription('Convert SF2 translation files into BigFoot translation files')
            ->addOption('no-vendor', null, InputOption::VALUE_NONE, 'Ne pas prendre les bundles dans le répertoire vendor/')
            ->addOption('bundle', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Liste des bundles à convertir, si non spécifié tous les bundles seront convertis')
            ->addOption('namespace', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Liste des namespaces à convertir, si non spécifié tous les namespaces seront convertis')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Effectue la convertion, sans cette option on ne fait rien')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $allBundles = $this->getContainer()->get('kernel')->getBundles();
        $noVendor = $input->getOption('no-vendor');
        $selectedBundles = $input->getOption('bundle');
        $selectedNamespaces = $input->getOption('namespace');
        $bundles = $allBundles;

        // on ne peut pas donner une liste de bundles, et une liste de namespaces
        if (count($selectedBundles) > 0 && count($selectedNamespaces) > 0) {
            throw new \Exception('Vous ne pouvez pas indiquer une liste de bundles, et une liste de namespaces.');
        }

        // filtrage des bundles à convertir selon les options
        if ($noVendor || count($selectedBundles) > 0 || count($selectedNamespaces) > 0) {
            foreach ($allBundles as $bundle) {
                $deleteBundle = false;
                // pas les vendor
                if ($noVendor && strpos($bundle->getPath(), '/vendor/') !== false) {
                    $deleteBundle = true;
                // liste de bundles donnée
                } else if (count($selectedBundles) > 0 && in_array($bundle->getName(), $selectedBundles) == false) {
                    $deleteBundle = true;
                // liste de namespaces donnée
                } else if (count($selectedNamespaces) > 0) {
                    $bundleIsInNamespaces = false;
                    foreach ($selectedNamespaces as $selectedNamespace) {
                        if (substr($bundle->getNamespace(), 0, strlen($selectedNamespace)) == $selectedNamespace) {
                            $bundleIsInNamespaces = true;
                            break;
                        }
                    }
                    $deleteBundle = !$bundleIsInNamespaces;
                }

                if ($deleteBundle) {
                    unset($bundles[$bundle->getName()]);
                }
            }
        }

        // message d'info sur les bundles à convertir
        if (count($bundles) == 0) {
            throw new \Exception('<error>Aucun bundle à convertir. Vérifiez vos paramètres.</error>');
        } else {
            $output->writeln('Bundles à convertir : ' . implode(', ', array_keys($bundles)));
        }

        // convertion
        if ($input->getOption('force')) {
            $progress = new ProgressBar($output, count($bundles));
            $this->getContainer()->get('bigfoot_core.translation.convert')->symfony2ToBigFoot($bundles, $progress);
            $progress->finish();
        // juste un message pour indiquer comment lancer la convertion
        } else {
            $output->writeln('Pour effectuer la convertion finale, il faut ajouter l\'option --force.');
        }
    }

}
