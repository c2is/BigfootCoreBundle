<?php

namespace Bigfoot\Bundle\CoreBundle\Manager;

use Bigfoot\Bundle\CoreBundle\Entity\TranslatableLabelRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\EntityManager;

/**
 * Csv manager
 */
class CsvManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param $entity
     * @param $fields
     * @return Response
     */
    public function generateCsv($entity, $fields)
    {
        $labelArray       = array('Identifiant');
        $entitySelections = array();

        foreach ($fields as $dbField => $options) {
            $labelArray[] = utf8_decode($options['label']);

            if (strpos($dbField, '.')) {
                $entitySelections[] = array(
                    'field'    => $dbField,
                    'external' => true,
                    'multiple' => isset($options['multiple']) ? $options['multiple'] : false,
                    'unique'   => isset($options['unique']) ? $options['unique'] : false,
                );
            } else {
                $entitySelections[] = array(
                    'field'    => $dbField,
                    'external' => false,
                    'multiple' => false
                );
            }
        }

        $handle = fopen('php://memory', 'r+');

        fputcsv($handle, $labelArray, ';');

        $csvQueryArray = $this->buildCsvQuery($entity, $entitySelections);

        foreach ($csvQueryArray as $csvElement) {
            fputcsv($handle, $csvElement, ';');
        }

        rewind($handle);

        $content = stream_get_contents($handle);

        fclose($handle);

        return new Response($content, 200, array(
            'Content-Type'        => 'application/force-download; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="export.csv"'
        ));
    }

    /**
     * @param $entity
     * @param $entitySelect
     * @param $externalSelect
     * @return mixed
     */
    private function buildCsvQuery($entity, $entitySelections)
    {
        $query = $this->entityManager->getRepository($entity)
            ->createQueryBuilder('e')
            ->select('e.id');

        $index = 1;

        foreach ($entitySelections as $entitySelection) {
            if ($entitySelection['external']) {
                $externalTempArray = explode('.', $entitySelection['field']);
                $externalEntity    = $externalTempArray[0];
                $externalField     = $externalTempArray[1];
                $entityPrefix      = substr($externalEntity, 0, 1) . $index;
                $alias             = ($entitySelection['multiple']) ? $entityPrefix . ucfirst($externalField) . (($entitySelection['unique']) ? 'YYY' : 'XXX') : $entityPrefix . ucfirst($externalField);

                $query             = $query
                    ->addSelect($entityPrefix . '.' . $externalField . ' as ' . $alias)
                    ->leftJoin('e.' . $externalEntity, $entityPrefix);

                $index++;
            } else {
                $query = $query
                    ->addSelect('e.' . $entitySelection['field']);
            }
        }

        $csvArray = $query->getQuery()->getResult();

        return $this->mergeClonedItem($csvArray);
    }

    /**
     * @param $csvArray
     * @param string $separator
     * @return array
     */
    private function mergeClonedItem($csvArray, $separator = ',')
    {
        $finalArray = array();

        foreach ($csvArray as $key => $element) {
            foreach ($element as $keyE => $value) {
                $value = utf8_decode($this->formatValue($value, $separator));

                if (strpos($keyE, 'XXX')) {
                    $finalArray[$element['id']][$keyE] = isset($finalArray[$element['id']][$keyE]) ? ($finalArray[$element['id']][$keyE] . ' ' . $separator . $value) : $value;
                } elseif (strpos($keyE, 'YYY')) {
                    $finalArray[$element['id']][$keyE][] = $value;
                } else {
                    $finalArray[$element['id']][$keyE] = $value;
                }
            }
        }

        foreach ($finalArray as &$element) {
            foreach ($element as $key => &$subElement) {
                if (is_array($subElement)) {
                    $element[$key] = implode($separator, array_unique($subElement));
                }
            }
        }

        return $finalArray;
    }

    /**
     * @param $value
     * @param $separator
     * @return string
     */
    private function formatValue($value, $separator)
    {
        if ($value instanceof \DateTime) {
            return $value->format('d-m-Y');
        } else if (is_array($value)) {
            return implode($separator, $value);
        }

        return $value;
    }
}
