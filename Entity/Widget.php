<?php

namespace Bigfoot\Bundle\CoreBundle\Entity;

use Bigfoot\Bundle\CoreBundle\Entity\Translation\WidgetTranslation;
use Bigfoot\Bundle\CoreBundle\WidgetInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Widget
 *
 * @Gedmo\TranslationEntity(class="Bigfoot\Bundle\CoreBundle\Entity\Translation\WidgetTranslation")
 * @ORM\Table(name="widget_backoffice")
 * @ORM\Entity
 */
class Widget
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    protected $title;

    /**
     * @ORM\OneToMany(targetEntity="Bigfoot\Bundle\CoreBundle\Entity\Widget\Parameter", mappedBy="widget", cascade={"persist", "remove", "merge"})
     */
    protected $parameters;

    /**
     * @Gedmo\Locale
     */
    private $locale;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Bigfoot\Bundle\CoreBundle\Entity\Translation\WidgetTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    private $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->parameters   = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Widget
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Widget
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Add parameters
     *
     * @param \Bigfoot\Bundle\CoreBundle\Entity\Widget\Parameter $parameters
     * @return Widget
     */
    public function addParameter(\Bigfoot\Bundle\CoreBundle\Entity\Widget\Parameter $parameters)
    {
        $this->parameters[] = $parameters;

        return $this;
    }

    /**
     * Remove parameters
     *
     * @param \Bigfoot\Bundle\CoreBundle\Entity\Widget\Parameter $parameters
     */
    public function removeParameter(\Bigfoot\Bundle\CoreBundle\Entity\Widget\Parameter $parameters)
    {
        $this->parameters->removeElement($parameters);
    }

    /**
     * Get parameters
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param $locale
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return mixed
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param WidgetTranslation $t
     */
    public function addTranslation(WidgetTranslation $t)
    {
        if (!$this->translations->contains($t)) {
            $this->translations[] = $t;
            $t->setObject($this);
        }
    }
}
