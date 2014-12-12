<?php

namespace Bigfoot\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoleMenu
 *
 * @ORM\Table(name="bigfoot_settings")
 * @ORM\Entity
 */
class Settings
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var array
     *
     * @ORM\Column(name="settings", type="array", nullable=true)
     */
    private $settings;

    /**
     * Gets the value of id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the value of settings.
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Sets the value of settings.
     *
     * @param array $settings the settings
     *
     * @return self
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * Get a setting
     *
     * @param  string $name
     * @param  mixed $default
     *
     * @return mixed
     */
    public function getSetting($name, $default = null)
    {
        if (isset($this->settings[$name])) {
            return $this->settings[$name];
        }

        return $default;
    }
}
