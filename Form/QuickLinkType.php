<?php

namespace Bigfoot\Bundle\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Bigfoot\Bundle\CoreBundle\Entity\QuickLink;

/**
 * Class QuickLinkType
 * @package Bigfoot\Bundle\CoreBundle\Form
 */
class QuickLinkType extends AbstractType
{
    private $securityContext;
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function setSecurityContext($securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->securityContext->getToken()->getUser();

        $builder
            ->add('userId','hidden',array(
                'data' => $user->getId()
            ))
            ->add('link','text',array(
                'data' => $this->request->headers->get('referer')
            ))
            ->add('labelLink')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bigfoot\Bundle\CoreBundle\Entity\QuickLink'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bigfoot_bundle_corebundle_quicklinktype';
    }
}
