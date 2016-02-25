<?php

namespace Bigfoot\Bundle\CoreBundle\Manager;

use Bigfoot\Bundle\CoreBundle\Entity\TranslatableLabelRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\EntityManager;

/**
 * Filter manager
 */
class FilterManager
{
    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var array
     */
    private $joins;

    /**
     * @var array
     */
    private $wheres;

    /**
     * @var integer
     */
    private $index;

    /**
     * Constructor
     *
     * @param FormFactory   $formFactory
     * @param EntityManager $entityManager
     * @param Session       $session
     * @param Request       $request
     */
    public function __construct(FormFactory $formFactory, EntityManager $entityManager, Session $session, Request $request)
    {
        $this->formFactory   = $formFactory;
        $this->entityManager = $entityManager;
        $this->session       = $session;
        $this->request       = $request;

        $this->joins         = array();
        $this->wheres        = array();

        $this->index         = 0;
    }

    /**
     * @param $entity
     * @return mixed|null
     */
    public function getSessionFilter($entity)
    {
        return !empty($entity) ? $this->session->get('bigfoot.crud.index.filters.'.$entity, null) : null;
    }

    /**
     * @param string $entity
     * @return bool
     */
    public function hasSessionFilter($entity)
    {
        $filters = $this->getSessionFilter($entity);
        if (is_array($filters)) {
            foreach ($this->getSessionFilter($entity) as $value) {
                if ($value !== null) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param string $entityName
     * @param array $globalFilters
     * @return bool
     */
    public function registerFilters($entityName, $globalFilters)
    {
        $form = $this->generateFilters($globalFilters, $entityName);
        $form->submit($this->request);

        $datas   = $form->getData();
        $filters = $globalFilters['fields'];

        if ($this->request->request->get('clear', null) != null) {
            $this->session->set('bigfoot.crud.index.filters.'.strtolower($entityName), array());

            return true;
        }

        foreach ($filters as $key => $filter) {
            if (isset($datas[$filter['name']]) && $datas[$filter['name']] !== null) {
                $data = $datas[$filter['name']];

                switch ($filter['type']) {
                    case 'entity':
                        $datas[$filter['name']] = $data->getId();

                        break;
                }
            }
        }

        $this->session->set('bigfoot.crud.index.filters.'.strtolower($entityName), $datas);

        return true;
    }

    /**
     * Filter query
     *
     * @param QueryBuilder $query
     * @param string $entityName
     * @param array $globalFilters
     *
     * @return QueryBuilder
     */
    public function filterQuery($query, $entityName, $globalFilters)
    {
        $datas = $this->session->get('bigfoot.crud.index.filters.'.strtolower($entityName), null);
        if ($datas) {
            $filters = $globalFilters['fields'];

            foreach ($filters as $key => $filter) {
                if (isset($datas[$filter['name']]) && $datas[$filter['name']] !== null) {
                    $options = $filter['options'];
                    $data = $datas[$filter['name']];

                    switch ($filter['type']) {
                        case 'repositoryMethod':
                            $em = $this->entityManager;
                            $repo = $em->getRepository($globalFilters['referer']);
                            call_user_func(array($repo, $options['method']), $query, $data);

                            break;
                        case 'entity':
                            $data = $this->getEntity($filter, $datas[$filter['name']]);
                            if ($alias = $this->hasJoin('e.'.$options['relation'])) {
                                $this->deleteJoin('e.'.$options['relation']);
                                $this->addJoin('e.'.$options['relation'], $alias, 'WITH _r'.$key.'.id = '.$data->getId());
                            } else {
                                $this->addJoin('e.'.$options['relation'], '_r'.$key, 'WITH _r'.$key.'.id = '.$data->getId());
                            }
                            break;
                        case 'choice':
                            $this->addWhere($options['property'], $data);
                            break;
                        case 'referer':
                            if (isset($options['type']) && $options['type'] == 'choice') {
                                $this->addWhere($options['property'], $data);
                            } else {
                                $this->addWhere($options['property'], $data, 'LIKE');
                            }
                            break;
                        case 'search':
                            $properties = $options['properties'];
                            $where      = array();

                            foreach ($properties as $property) {
                                $data = '%'.$data.'%';
                                if (preg_match('/^.*\..*$/i', $property)) {
                                    $property = explode('.', $property);
                                    $relation = $property[0];
                                    $property = $property[1];

                                    if ($alias = $this->hasJoin('e.'.$relation)) {
                                        $where[] = array('property' => $alias.'.'.$property, 'value' => $data);
                                    } else {
                                        $this->addJoin('e.'.$relation, '_r'.$key);
                                        $where[] = array('property' => '_r'.$key.'.'.$property, 'value' => $data);
                                    }
                                } else {
                                    $where[] = array('property' => 'e.'.$property, 'value' => $data);
                                }
                            }

                            $this->addWhere($where, null, 'OR_LIKE');
                            break;
                        case 'date_min':
                            $this->addWhere($options['property'], $data->format('Y-m-d'), 'MIN');
                            break;
                        case 'date':
                            $this->addWhere($options['property'], $data->format('Y-m-d'));
                            break;
                    }
                }
            }
        }

        return $this->hydrateQuery($query);
    }

    /**
     * Hydrate query
     *
     * @param  QueryBuilder $query
     *
     * @return QueryBuilder
     */
    private function hydrateQuery($query)
    {
        foreach ($this->joins as $join) {
            $query
                ->innerjoin($join['relation'].' '.$join['alias'], $join['on']);
        }

        foreach ($this->wheres as $where) {
            if ($where['type'] == null) {
                $query
                    ->andWhere($where['alias'].'.'.$where['property'].' = :v'.$this->index)
                    ->setParameter('v'.$this->index, $where['value'])
                ;
            } else {
                switch ($where['type']) {
                    case 'LIKE':
                        $query
                            ->andWhere($where['alias'].'.'.$where['property'].' LIKE :v'.$this->index)
                            ->setParameter('v'.$this->index, '%'.$where['value'].'%')
                        ;
                        break;
                    case 'OR_LIKE':
                        $expr = $query->expr()->orX();
                        foreach ($where['property'] as $property) {
                            $expr->add($query->expr()->like($property['property'], ':v'.$this->index));
                            $query->setParameter('v'.$this->index, $property['value']);
                            $this->index++;
                        }
                        $query->andWhere($expr);

                        break;
                    case 'MIN':
                        $query
                            ->andWhere($where['alias'].'.'.$where['property'].' >= :v' . $this->index)
                            ->setParameter('v' . $this->index, '%' . $where['value'] . '%')
                        ;
                        break;
                }
            }

            $this->index++;
        }

        return $query;
    }

    /**
     * @param array $datas
     * @param string $key
     * @return bool|\Symfony\Component\Form\Form|FormInterface
     * @throws \Exception
     */
    public function generateFilters($datas, $key)
    {
        $filters = array();

        if (!isset($datas['fields'])) {
            return false;
        }

        $fields  = $datas['fields'];
        $referer = $datas['referer'];

        foreach ($fields as $field) {
            $options = isset($field['options']) ? $field['options'] : array();

            switch ($field['type']) {
                case 'repositoryMethod':
                    if (!isset($options['method'])) {
                        throw new \Exception("You must define a repository method to call to apply the filter");
                    }
                    if (!isset($options['choicesMethod'])) {
                        throw new \Exception("You must define a repository method to call to generate the choices list");
                    }

                    $em = $this->entityManager;
                    /** @var TranslatableLabelRepository $repo */
                    $repo = $em->getRepository($datas['referer']);
                    $field['options']['choices'] = $repo->getCategories();

                    $filters[] = $field;
                    break;
                case 'choice':
                    if (!isset($options['choices'])) {
                        throw new \Exception("You must define an array of choices");
                    }
                    if (!is_array($options['choices'])) {
                        throw new \Exception("You must define an array of choices");
                    }
                    if (!isset($options['property'])) {
                        throw new \Exception("You must define the attribute to display for entity ".$options['entity']);
                    }

                    $filters[] = $field;
                    break;
                case 'entity':
                    if (!isset($options['class'])) {
                        throw new \Exception("You must define the entity namesapce (ie. AcmeDemoBundle:Acme)");
                    }
                    if (!isset($options['relation'])) {
                        throw new \Exception("You must define the relation between the entity to mapped and ".$options['entity']);
                    }
                    if (!isset($options['property'])) {
                        throw new \Exception("You must define the attribute to display for entity ".$options['entity']);
                    }

                    $filters[] = $field;
                    break;
                case 'referer':
                    if (!is_string($referer) || !preg_match('/^.*:.*$/i', $referer)) {
                        throw new \Exception("You must define the attribute to display for entity ".$options['entity']);
                    }
                    if (!isset($options['property'])) {
                        throw new \Exception("You must define the attribute to display for entity ".$options['entity']);
                    }

                    $type = isset($options['type']) ? $options['type'] : 'text';

                    $field['options']['type'] = $type;

                    if ($type != 'text') {
                        $field['options']['choices'] = $this->getChoices($referer, $options['property']);
                    }

                    $filters[] = $field;
                    break;
                case 'search':
                    if (!isset($options['properties'])) {
                        throw new \Exception("You must define an array of properties to search in for entity ".$options['entity']);
                    }
                    if (!is_array($options['properties'])) {
                        throw new \Exception("You must define an array of properties to search in for entity ".$options['entity']);
                    }

                    $filters[] = $field;
                    break;
                case 'date_min':
                    if (!isset($options['property'])) {
                        throw new \Exception("You must define the attribute to display for entity ".$options['entity']);
                    }

                    $filters[] = $field;
                    break;
                case 'date':
                    if (!isset($options['property'])) {
                        throw new \Exception("You must define the attribute to display for entity ".$options['entity']);
                    }

                    $filters[] = $field;
                    break;
            }
        }

        $form = $this->formFactory->create('bigfoot_core_filter_type', null, array('filters' => $filters, 'entity' => $key));

        return $form;
    }

    /**
     * Get Entity
     *
     * @param  array   $filter
     * @param  integer $id
     *
     * @return mixed
     */
    public function getEntity($filter, $id)
    {
        $options = $filter['options'];

        return $this->entityManager->getRepository($options['class'])->find($id);
    }

    /**
     * Get choices
     * Used to get values for the referer type
     *
     * @param  string $referer
     * @param  string $attribute
     *
     * @return array
     */
    private function getChoices($referer, $attribute)
    {
        $final   = array();

        $results = $this->entityManager
            ->getRepository($referer)
            ->createQueryBuilder('e')
            ->select('DISTINCT e.'.$attribute)
            ->getQuery()
            ->getArrayResult();

        foreach ($results as $result) {
            $value = current($result);

            $final[$value] = $value;
        }

        return $final;
    }

    /**
     * If joins already contains the join
     *
     * @param  string  $join
     *
     * @return boolean
     */
    public function hasJoin($join)
    {
        if (isset($this->joins[$join])) {
            return $this->joins[$join]['alias'];
        }

        return false;
    }

    /**
     * If joins already contains the join
     *
     * @param  string  $join
     *
     * @return boolean
     */
    public function deleteJoin($join)
    {
        unset($this->joins[$join]);

        return true;
    }

    /**
     * Add join
     *
     * @param string $join
     * @param string $alias
     *
     * @return FilterManager
     */
    public function addJoin($join, $alias = null, $on = null)
    {
        if (empty($alias)) {
            $alias = '_j'.$this->index;
            $this->index++;
        }

        $add                = array();
        $add['relation']    = $join;
        $add['alias']       = $alias;
        $add['on']          = $on;

        $this->joins[$join] = $add;

        return $this;
    }

    /**
     * Add where condition
     *
     * @param string $where
     * @param string $value
     * @param string $type
     * @param string $alias
     *
     * @return FilterManager
     */
    public function addWhere($where, $value, $type = null, $alias = 'e')
    {
        $add             = array();
        $add['property'] = $where;
        $add['value']    = $value;
        $add['type']     = $type;
        $add['alias']    = $alias;

        $this->wheres[]  = $add;

        return $this;
    }

    /**
     * @param $entityName
     * @return mixed
     */
    public function clearFilters($entityName)
    {
        return $this->session->remove('bigfoot.crud.index.filters.'.strtolower($entityName));
    }
}
