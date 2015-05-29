<?php

namespace Bigfoot\Bundle\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Translation\MessageSelector;

/**
 * Class TranslatableLabel
 *
 * @Gedmo\TranslationEntity(class="Bigfoot\Bundle\CoreBundle\Entity\TranslatableLabelTranslation")
 * @ORM\Entity(repositoryClass="Bigfoot\Bundle\CoreBundle\Entity\TranslatableLabelRepository")
 * @ORM\Table(name="bigfoot_translatable_label", uniqueConstraints={@ORM\UniqueConstraint(name="unique_name", columns={"name", "domain"})})
 * @package Bigfoot\Bundle\CoreBundle\Entity
 */
class TranslatableLabel
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
     * @Gedmo\Translatable
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="plural", type="boolean")
     */
    private $plural = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="multiline", type="boolean")
     */
    private $multiline = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="richtext", type="boolean", nullable=true)
     */
    private $richtext = false;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="edited_at", type="datetime", nullable=true)
     */
    private $editedAt;

    /**
     * @ORM\OneToMany(
     *   targetEntity="TranslatableLabelTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    private $translations;

    /**
     * @var string
     *
     * @Gedmo\Locale
     */
    private $locale;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * @param $id
     * @return $this
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
     * @param $domain
     * @return $this
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
     * @param $name
     * @return $this
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
     * @param $value
     * @return $this
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
     * @param $description
     * @return $this
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
     * @param boolean $plural
     * @return $this
     */
    public function setPlural($plural)
    {
        $this->plural = $plural;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isPlural()
    {
        return $this->plural;
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
     * @param boolean $richtext
     * @return $this
     */
    public function setRichtext($richtext)
    {
        $this->richtext = $richtext;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isRichtext()
    {
        return $this->richtext;
    }

    /**
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
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
     * @return mixed
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param TranslatableLabelTranslation $t
     */
    public function addTranslation(TranslatableLabelTranslation $t)
    {
        if (!$this->translations->contains($t)) {
            $this->translations[] = $t;
            $t->setObject($this);
        }
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        if (strpos($this->getName(), '.') === false) {
            return $this->getName();
        }
        return substr($this->getName(), 0, strpos($this->getName(), '.', strpos($this->getName(), '.') + 1));
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param $locale
     * @return $this
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    public function setTranslations($translations)
    {
        $this->translations = $translations;
        return $this;
    }
}
