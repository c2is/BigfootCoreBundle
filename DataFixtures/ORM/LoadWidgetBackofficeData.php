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
        $widgetParam1->setValue(3);
        $widgetParam1->setWidget($widgetRecentActivity);
        $manager->persist($widgetParam1);
        $widgetParam1 = new WidgetBackoffice\Parameter();
        $widgetParam1->setName('width');
        $widgetParam1->setValue(6);
        $widgetParam1->setWidget($widgetRecentActivity);
        $manager->persist($widgetParam1);

        $widgetRecentActivity = new WidgetBackoffice();
        $widgetRecentActivity->setName('Bigfoot\Bundle\CoreBundle\Widget\SecondTest');
        $widgetRecentActivity->setTitle('Second test');
        $manager->persist($widgetRecentActivity);

        $widgetParam2 = new WidgetBackoffice\Parameter();
        $widgetParam2->setName('order');
        $widgetParam2->setValue(0);
        $widgetParam2->setWidget($widgetRecentActivity);
        $manager->persist($widgetParam2);
        $widgetParam2 = new WidgetBackoffice\Parameter();
        $widgetParam2->setName('width');
        $widgetParam2->setValue(6);
        $widgetParam2->setWidget($widgetRecentActivity);
        $manager->persist($widgetParam2);

        $widgetWidgetTest = new WidgetBackoffice();
        $widgetWidgetTest->setName('Bigfoot\Bundle\CoreBundle\Widget\WidgetTest');
        $widgetWidgetTest->setTitle('Another test');
        $manager->persist($widgetWidgetTest);

        $widgetParam3 = new WidgetBackoffice\Parameter();
        $widgetParam3->setName('order');
        $widgetParam3->setValue(2);
        $widgetParam3->setWidget($widgetWidgetTest);
        $manager->persist($widgetParam3);
        $widgetParam3 = new WidgetBackoffice\Parameter();
        $widgetParam3->setName('width');
        $widgetParam3->setValue(6);
        $widgetParam3->setWidget($widgetWidgetTest);
        $manager->persist($widgetParam3);

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