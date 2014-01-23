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
    protected  $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

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

            $params = $tmpWidget->getParameters();
            foreach ($params as $param) {
                $widget->setParam($param->getName(), $param->getValue());
            }
            $widgets[] = $widget;
        }

        return $widgets;
    }
}