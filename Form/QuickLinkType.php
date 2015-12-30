<?php

namespace Bigfoot\Bundle\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Bigfoot\Bundle\CoreBundle\Entity\QuickLink;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class QuickLinkType
 * @package Bigfoot\Bundle\CoreBundle\Form
 */
class QuickLinkType extends AbstractType
{
    /** @var  TokenStorage */
    private $securityTokenStorage;
    private $request;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function setSecurityTokenStorage($securityTokenStorage)
    {
        $this->securityTokenStorage = $securityTokenStorage;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->securityTokenStorage->getToken()->getUser();

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
    public function configureOptions(OptionsResolver $resolver)
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
