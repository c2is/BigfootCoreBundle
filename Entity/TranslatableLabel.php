<?php

namespace Bigfoot\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class TranslatableLabel
 * @ORM\Entity
 * @ORM\Table(name="bigfoot_translatable_label", uniqueConstraints={@ORM\UniqueConstraint(name="unique_name_domain", columns={"name", "domain"})})
 * @package Bigfoot\Bundle\CoreBundle\Entity
 */
class TranslatableLabel
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="domain", type="string", length=255, options={"default":"messages"})
     */
    private $domain = 'messages';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="value", type="string", length=255)
     */
    private $value;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     *  var boolean
     *
     * @ORM\Column(name="is_pluralization", type="boolean")
     */
    private $isPluralization = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_multilines", type="boolean")
     */
    private $isMultilines = false;

    /**
     * @Gedmo\Locale
     */
    protected $locale;

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set isPluralization
     *
     * @param boolean $isPluralization
     * @return TranslatableLabel
     */
    public function setIsPluralization($isPluralization)
    {
        $this->isPluralization = $isPluralization;

        return $this;
    }

    /**
     * Get isPluralization
     *
     * @return boolean
     */
    public function getIsPluralization()
    {
        return $this->isPluralization;
    }

    /**
     * Set isMultilines
     *
     * @param boolean $isMultilines
     * @return TranslatableLabel
     */
    public function setIsMultilines($isMultilines)
    {
        $this->isMultilines = $isMultilines;

        return $this;
    }

    /**
     * Get isMultilines
     *
     * @return boolean
     */
    public function getIsMultilines()
    {
        return $this->isMultilines;
    }

    /**
     * @param $locale
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
}