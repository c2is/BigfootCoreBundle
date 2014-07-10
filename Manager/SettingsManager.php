<?php

namespace Bigfoot\Bundle\CoreBundle\Manager;

use Doctrine\ORM\EntityManager;

/**
 * Settings manager
 */
class SettingsManager
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $em)
    {
        $settings = $em->getRepository('BigfootCoreBundle:Settings')->findAll();

        $this->settings = current($settings);
    }

    public function getSetting($name, $default = null)
    {
        return $this->settings->getSetting($name, $default);
    }
}
