<?php
/**
 * Created by PhpStorm.
 * User: splancon
 * Date: 21/01/14
 * Time: 15:02
 */

namespace Bigfoot\Bundle\CoreBundle\Controller;


use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\ORM\Query;

class DashboardController extends ContainerAware
{
    public function getBoard()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->container->get('doctrine');

        $queryBuilder = $em->getRepository('BigfootCoreBundle:Widget')
            ->createQueryBuilder('w');
        $queryBuilder
            ->join('w.parameters', 'p')
            ->Where('p.user = '.$user->getId().' OR '.$queryBuilder->expr()->isNull('p.user'))
            ->andWhere("p.name='order'")
            ->orderBy('p.value * 1', 'ASC');

        $query = $queryBuilder->getQuery();
        $query->setHint(
            Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
        );

        $tmpWidgets = $query->getResult();

        $widgets = array();
        foreach ($tmpWidgets as $tmpWidget) {
            $widgetClass = $tmpWidget->getName();
            $widget = new $widgetClass($this->container);
            $widget->setTitle($tmpWidget->getTitle());

            $queryBuilder = $em->getRepository('BigfootCoreBundle:Widget\Parameter')
                ->createQueryBuilder('p');
            $queryBuilder
                ->Where('p.user = '.$user->getId().' OR '.$queryBuilder->expr()->isNull('p.user'))
                ->andWhere("p.widget = " . $tmpWidget->getId())
                ->orderBy('p.user', 'DESC');
            $query = $queryBuilder->getQuery();

            $params = $query->getResult();
            foreach ($params as $param) {
                if (!$widget->hasParam($param->getName())) {
                    $widget->setParam($param->getName(), $param->getValue());
                }
            }
            $widgets[] = $widget;
        }
        usort($widgets, array(__CLASS__, 'sortWidgets'));

        return $widgets;
    }

    static function sortWidgets($w1, $w2)
    {
        if ($w1->getParam('order') == $w2->getParam('order')) {
            return 0;
        }
        return ($w1->getParam('order') < $w2->getParam('order')) ? -1 : 1;
    }
}