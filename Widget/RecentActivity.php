<?php
/**
 * Created by PhpStorm.
 * User: splancon
 * Date: 21/01/14
 * Time: 18:05
 */

namespace Bigfoot\Bundle\CoreBundle\Widget;

use Bigfoot\Bundle\CoreBundle\Model\AbstractWidget;
use Symfony\Component\Validator\Constraints\GreaterThan;

class RecentActivity extends AbstractWidget
{
    public $tabs = array();

    public function renderContent()
    {
        return $this->container->get('templating')->render(sprintf('%s:widget:recentActivity.html.twig', $this->container->getParameter('bigfoot.theme.bundle')), array('widget' => $this));
    }

    public function render()
    {
        $em = $this->container->get('doctrine');
        $tabs = unserialize($this->getParam('tabs'));

        $dateHistory = new \DateTime(date('Y-m-d', time()), new \DateTimeZone('Europe/Paris'));
        $dateHistory->sub(new \DateInterval('P7D'));

        foreach ($tabs as $tab) {
            if ($this->entityCanBeFollow($tab['entity'])) {
                $queryBuilder = $em->getRepository($tab['entity'])->createQueryBuilder('e')
                    ->where('e.updated >= '.$dateHistory->format('Y-m-d'));
                $query = $queryBuilder->getQuery();
                $entities = $query->getResult();

                foreach ($entities as $entity) {
                    $objectController = new $tab['controller'];

                    $timelineItem = array(
                        'time' => $entity->getUpdated()->format('H:i'),
                        'username' => $entity->getUpdatedBy(),
                        'name' => $entity->getName(),
                    );

                    // Create edit link if controller declared for entity
                    if (isset($tab['controller']) && class_exists($tab['controller'])) {
                        $timelineItem['edit_link'] = $this->container->get('router')->generate($objectController->getRouteNameForAction('edit'), array('id' => $entity->getId()));
                    }
                    if ($entity->getUpdated() == $entity->getCreated()) {
                        $timelineItem['desc'] = $this->container->get('translator')->trans('%username% created element %name%.');
                        $tab['timeline'][$entity->getCreated()->format('Y-m-d')][] = $timelineItem;
                    } else {
                        $timelineItemUpdate = $timelineItem;
                        $timelineItemUpdate['desc'] = $this->container->get('translator')->trans('%username% updated element %name%.');
                        $tab['timeline'][$entity->getUpdated()->format('Y-m-d')][] = $timelineItemUpdate;

                        $timelineItem['desc'] = $this->container->get('translator')->trans('%username% created element %name%.');
                        $tab['timeline'][$entity->getCreated()->format('Y-m-d')][] = $timelineItem;
                    }

                }
                if (isset($tab['timeline']) && is_array($tab['timeline'])) {
                    krsort($tab['timeline']);
                    foreach (array_keys($tab['timeline']) as $date) {
                        usort($tab['timeline'][$date], array(__CLASS__, 'sortTimeline'));
                    }
                }
                $content = $this->renderTab($tab);
            } elseif ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                $content = $this->renderTab($tab, 'error');
            } else {
                $content = "";
            }

            if ($content != "") {
                $this->tabs[$tab['name']] = array(
                    'title' => $tab['title'],
                    'content' => $content,
                );
            }
        }

        return parent::render();
    }

    private function entityCanBeFollow($entityName)
    {
        return class_exists($entityName) && property_exists($entityName, 'created') && property_exists($entityName, 'updated') && property_exists($entityName, 'createdBy') && property_exists($entityName, 'updatedBy');
    }

    private function renderTab($tab, $type = "")
    {
        return $this->container->get('templating')->render(sprintf('%s:widget:recentActivity.tab.html.twig', $this->container->getParameter('bigfoot.theme.bundle')), array('tab' => $tab, 'type_tab' => $type));
    }

    private function sortTimeline($t1, $t2)
    {
        if ($t1['time'] == $t2['time']) {
            return 0;
        }
        return ($t1['time'] > $t2['time']) ? -1 : 1;
    }
}