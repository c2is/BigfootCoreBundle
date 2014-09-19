<?php

namespace Bigfoot\Bundle\CoreBundle\Manager;

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
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Get filter in session
     *
     * @param  string $entity
     *
     * @return mixed
     */
    public function getSessionFilter($entity)
    {
        return !empty($entity) ? $this->session->get('bigfoot.crud.index.filters.'.$entity, null) : null;
    }

    /**
     * @param Request $request
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
                $data    = $datas[$filter['name']];

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
     * @param  QueryBuilder $query
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
                            $repo = $this->entityManager->getRepository($globalFilters['referer']);
                            $repo->{$options['method']}($query, $datas[$filter['name']]);
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
                                if (preg_match('/^.*\..*$/i', $property)) {
                                    $property = explode('.', $property);
                                    $relation = $property[0];
                                    $property = $property[1];

                                    if ($alias = $this->hasJoin('e.'.$relation)) {
                                        $where[] = $alias.'.'.$property.' LIKE \'%'.$data.'%\'';
                                    } else {
                                        $this->addJoin('e.'.$relation, '_r'.$key);

                                        $where[] = '_r'.$key.'.'.$property.' LIKE \'%'.$data.'%\'';
                                    }
                                } else {
                                    $where[] = 'e.'.$property.' LIKE \'%'.$data.'%\'';
                                }
                            }

                            $this->addWhere(implode(' OR ', $where), null, 'LITERAL');
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
                    ->setParameter('v'.$this->index, $where['value']);

                $this->index++;
            } else {
                switch ($where['type']) {
                    case 'LIKE':
                        $query
                            ->andWhere($where['alias'].'.'.$where['property'].' LIKE \'%'.$where['value'].'%\'');
                        break;
                    case 'LITERAL':
                        $query
                            ->andWhere($where['property']);
                        break;
                }
            }
        }

        return $query;
    }

    /**
     * Generate filters
     *
     * @param  array $datas
     *
     * @return FormInterface
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
                    if (!isset($options['choicesMethod'])) {
                        throw new \Exception("You must define a repository method to generate the list of choices");
                    }

                    $field['referer'] = $datas['referer'];
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
}
