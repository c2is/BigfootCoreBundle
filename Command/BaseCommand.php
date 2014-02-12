<?php

namespace Bigfoot\Bundle\CoreBundle\Command;

use Doctrine\ORM\NoResultException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends ContainerAwareCommand
{
    /**
     * @var \Doctrine\ORM\EntityManager $entityManager
     */
    private $entityManager;

    /**
     * @var \Bigfoot\Bundle\CoreBundle\Manager\RouteManager $routeManager
     */
    private $routeManager;

    /**
     * @var OutputInterface $output
     */
    private $output;

    /**
     * Configure
     */
    protected function configure()
    {
    }

    /**
     * Initializes the command just after the input has been validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->routeManager  = $this->getContainer()->get('bigfoot_core.manager.route');
        $this->output        = $output;
    }

    /**
     * @return \Doctrine\ORM\EntityManager $entityManager
     */
    protected function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @return EntityRepository
     */
    protected function getRepository($class)
    {
        return $this->entityManager->getRepository($class);
    }

    /**
     * @return \Bigfoot\Bundle\CoreBundle\Manager\RouteManager $routeManager
     */
    protected function getRouteManager()
    {
        return $this->routeManager;
    }

    /**
     * @return Translation Repository
     */
    protected function getTranslationRepository()
    {
        return $this->getRepository('Gedmo\Translatable\Entity\Translation');
    }
}
