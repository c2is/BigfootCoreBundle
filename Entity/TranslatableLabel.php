<?php

namespace Bigfoot\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class TranslatableLabel
 * @ORM\Entity(repositoryClass="Bigfoot\Bundle\CoreBundle\Entity\TranslatableLabelRepository")
 * @ORM\Table(name="bigfoot_translatable_label", uniqueConstraints={@ORM\UniqueConstraint(name="unique_name_locale", columns={"name", "domain"})})
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
     * @ORM\Column(name="value", type="text")
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var boolean
     *
     * @ORM\Column(name="multiline", type="boolean")
     */
    private $multiline = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="pluralized", type="boolean")
     */
    private $pluralized = false;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="edited_at", type="datetime")
     */
    private $editedAt;

    /**
     * @var string
     * @Gedmo\Locale
     */
    private $locale;

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
     * @param string $locale
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
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
     * @param boolean $multiline
     * @return $this
     */
    public function setMultiline($multiline)
    {
        $this->multiline = $multiline;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isMultiline()
    {
        return $this->multiline;
    }

    /**
     * @param boolean $pluralized
     * @return $this
     */
    public function setPluralized($pluralized)
    {
        $this->pluralized = $pluralized;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isPluralized()
    {
        return $this->pluralized;
    }

    /**
     * @param \DateTime $editedAt
     * @return $this
     */
    public function setEditedAt($editedAt)
    {
        $this->editedAt = $editedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEditedAt()
    {
        return $this->editedAt;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        $posSecondDot = strpos($this->name, '.', strpos($this->name, '.')+1);
        return $posSecondDot !== false ? substr($this->name, 0, $posSecondDot) : $this->name;
    }
}
