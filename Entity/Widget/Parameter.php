<?php

namespace Bigfoot\Bundle\CoreBundle\Entity\Widget;

use Doctrine\ORM\Mapping as ORM;

/**
 * Param
 *
 * @ORM\Table(name="widget_backoffice_parameter")
 * @ORM\Entity
 */
class Parameter
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
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
     * @ORM\Column(name="value", type="string", length=255)
     */
    protected $value;

    /**
     * @ORM\ManyToOne(targetEntity="Bigfoot\Bundle\CoreBundle\Entity\Widget", inversedBy="parameters", cascade={"persist"})
     * @ORM\JoinColumn(name="widget_backoffice_id", referencedColumnName="id")
     */
    protected $widget;

    /**
     * @ORM\ManyToOne(targetEntity="Bigfoot\Bundle\UserBundle\Entity\BigfootUser", cascade={"persist"})
     * @ORM\JoinColumn(name="bigfoot_user_id", referencedColumnName="id")
     */
    protected $user;

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
     * @return Parameter
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
     * Set value
     *
     * @param string $value
     * @return Bigfoot\Bundle\CoreBundle\Entity\Widget\WidgetParam
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set widget
     *
     * @param \Bigfoot\Bundle\CoreBundle\Entity\Widget $widget
     * @return Parameter
     */
    public function setWidget(\Bigfoot\Bundle\CoreBundle\Entity\Widget $widget = null)
    {
        $this->widget = $widget;
    
        return $this;
    }

    /**
     * Get widget
     *
     * @return \Bigfoot\Bundle\CoreBundle\Entity\Widget 
     */
    public function getWidget()
    {
        return $this->widget;
    }

    /**
     * Set user
     *
     * @param \Bigfoot\Bundle\UserBundle\Entity\BigfootUser $user
     * @return Parameter
     */
    public function setUser(\Bigfoot\Bundle\UserBundle\Entity\BigfootUser $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Bigfoot\Bundle\UserBundle\Entity\BigfootUser 
     */
    public function getUser()
    {
        return $this->user;
    }
}