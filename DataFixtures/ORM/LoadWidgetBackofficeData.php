<?php
/**
 * Created by PhpStorm.
 * User: splancon
 * Date: 21/01/14
 * Time: 15:05
 */

namespace Bigfoot\Bundle\CoreBundle\DataFixtures\ORM;


use Bigfoot\Bundle\CoreBundle\Entity\Widget as WidgetBackoffice;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadWidgetBackofficeData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {/*
        $widgetRecentActivity = new WidgetBackoffice();
        $widgetRecentActivity->setName('Bigfoot\Bundle\CoreBundle\Widget\RecentActivity');
        $widgetRecentActivity->setTitle('Recent activity');

        $repository = $manager->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        $repository->translate($widgetRecentActivity, 'title', 'fr', 'Activté récente');

        $manager->persist($widgetRecentActivity);

        $widgetParam1 = new WidgetBackoffice\Parameter();
        $widgetParam1->setName('order');
        $widgetParam1->setValue(1);
        $widgetParam1->setWidget($widgetRecentActivity);
        $manager->persist($widgetParam1);

        $widgetParam1 = new WidgetBackoffice\Parameter();
        $widgetParam1->setName('width');
        $widgetParam1->setValue(6);
        $widgetParam1->setWidget($widgetRecentActivity);
        $manager->persist($widgetParam1);

        $widgetParam1 = new WidgetBackoffice\Parameter();
        $widgetParam1->setName('tabs');
        $widgetParam1->setValue(serialize(array(
            array(
                'name' => 'page',
                'title' => 'Pages',
                'entity' => 'Bigfoot\Bundle\ContentBundle\Entity\Page',
                'controller' => 'Bigfoot\Bundle\ContentBundle\Controller\PageController'),
            array(
                'name' => 'widget',
                'title' =>'Widgets',
                'entity' => 'Bigfoot\Bundle\ContentBundle\Entity\Widget',
                'controller' => 'Bigfoot\Bundle\ContentBundle\Controller\WidgetController'),
            array(
                'name' => 'staticContent',
                'title' =>'Static Content',
                'entity' => 'Bigfoot\Bundle\ContentBundle\Entity\StaticContent',
                'controller' => 'Bigfoot\Bundle\ContentBundle\Controller\StaticContentController'),
            array(
                'name' => 'user',
                'title' =>'Users',
                'entity' => 'Bigfoot\Bundle\UserBundle\Entity\User',
                'controller' => 'Bigfoot\Bundle\UserBundle\Controller\UserController'),
            array(
                'name' => 'menuItem',
                'title' =>'Menu Item',
                'entity' => 'Bigfoot\Bundle\NavigationBundle\Entity\Item',
                'controller' => 'Bigfoot\Bundle\NavigationBundle\Controller\ItemController'),
        )));
        $widgetParam1->setWidget($widgetRecentActivity);
        $manager->persist($widgetParam1);

        $manager->flush();*/
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 55;
    }
}