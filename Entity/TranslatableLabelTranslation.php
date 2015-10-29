<?php

namespace Bigfoot\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

/**
 * @ORM\Entity
 * @ORM\Table(name="bigfoot_translatable_label_translations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="lookup_unique_idx_translation", columns={
 *         "locale", "object_id", "field"
 *     })}
 * )
 */
class TranslatableLabelTranslation extends AbstractPersonalTranslation
{
    /**
     * Convenient constructor
     *
     * @param string $locale
     * @param string $field
     * @param string $value
     */
    public function __construct($locale, $field, $value)
    {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($value);
        $this->emptyValue = true;
    }

    /**
     * @var boolean
     *
     * @ORM\Column(name="empty_value", type="boolean", options={"default": false})
     */
    private $emptyValue;

    /**
     * @ORM\ManyToOne(targetEntity="TranslatableLabel", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->content;
    }

    /**
     * @return boolean
     */
    public function isEmptyValue()
    {
        return $this->emptyValue;
    }

    /**
     * @param boolean $emptyValue
     * @return self
     */
    public function setEmptyValue($emptyValue)
    {
        $this->emptyValue = $emptyValue;
        return $this;
    }
}
