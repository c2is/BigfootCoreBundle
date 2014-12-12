<?php

namespace Bigfoot\Bundle\CoreBundle\Entity;

use Bigfoot\Bundle\CoreBundle\WidgetInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Widget
 *
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
     * Constructor
     */
    public function __construct()
    {
        $this->parameters = new \Doctrine\Common\Collections\ArrayCollection();
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
}