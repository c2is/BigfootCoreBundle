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
    {
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
        $widgetParam1->setName('follow_entities');
        $widgetParam1->setValue(serialize(array('Bigfoot\Bundle\ContentBundle\Entity\Page')));
        $widgetParam1->setWidget($widgetRecentActivity);
        $manager->persist($widgetParam1);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 55;
    }
}