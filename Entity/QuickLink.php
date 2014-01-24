<?php

namespace Bigfoot\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * QuickLink
 *
 * @ORM\Table("bigfoot_quicklink")
 * @ORM\Entity(repositoryClass="Bigfoot\Bundle\CoreBundle\Entity\QuickLinkRepository")
 */
class QuickLink
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
     * @var integer
     *
     * @ORM\Column(name="userId", type="integer")
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=255)
     */
    private $link;

    /**
     * @var string
     *
     * @ORM\Column(name="labelLink", type="string", length=255)
     */
    private $labelLink;


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
     * Set userId
     *
     * @param integer $userId
     * @return QuickLink
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    
        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set link
     *
     * @param string $link
     * @return QuickLink
     */
    public function setLink($link)
    {
        $this->link = $link;
    
        return $this;
    }

    /**
     * Get link
     *
     * @return string 
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set labelLink
     *
     * @param string $labelLink
     * @return QuickLink
     */
    public function setLabelLink($labelLink)
    {
        $this->labelLink = $labelLink;
    
        return $this;
    }

    /**
     * Get labelLink
     *
     * @return string 
     */
    public function getLabelLink()
    {
        return $this->labelLink;
    }
}
