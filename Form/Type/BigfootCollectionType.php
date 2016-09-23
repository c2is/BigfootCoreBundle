<?php

namespace Bigfoot\Bundle\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class BigfootCollectionType extends AbstractType
{
    public function getParent()
    {
        return CollectionType::class;
    }
}
