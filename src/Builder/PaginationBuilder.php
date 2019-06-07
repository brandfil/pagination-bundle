<?php

namespace Brandfil\BrandfilPaginationBundle\Builder;

use Brandfil\BrandfilPaginationBundle\Model\Database\OrderProperty;
use Brandfil\BrandfilPaginationBundle\Model\PaginatorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;

/**
 * Class PaginationBuilder
 * @package Brandfil\BrandfilPaginationBundle\Builder
 */
class PaginationBuilder implements PaginationBuilderInterface
{
    /**
     * @var int
     */
    private $limit;

    /**
     * @var int
     */
    private $page;

    /**
     * @var Collection
     */
    private $orderProperties;

    /**
     * @var Collection
     */
    private $properties;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * @var bool
     */
    private $countRows;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var string
     */
    private $alias = 'o';

    /**
     * @var string
     */
    private $cursor;

    /**
     * @var mixed
     */
    private $model;


    public function __construct(ManagerRegistry $doctrine, PaginatorInterface $paginator)
    {
        $this->doctrine = $doctrine;
        $this->paginator = $paginator;
        $this->orderProperties = new ArrayCollection();
        $this->properties = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function limit(int $limit): PaginationBuilderInterface
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function page(int $page): PaginationBuilderInterface
    {
        $this->page = $page > 0 ? $page : 1;
        $this->paginator->setCurrentPage($page);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function countRows(bool $count = false): PaginationBuilderInterface
    {
        $this->countRows = $count;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function registerPropertyName(string $property): PaginationBuilderInterface
    {
        $this->properties->set($property, null);
    }

    /**
     * {@inheritdoc}
     */
    public function orderBy(OrderProperty $property): PaginationBuilderInterface
    {
        $this->orderProperties->add($property);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCursor(string $cursor = null): PaginationBuilderInterface
    {
        $this->cursor = $cursor;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function model(string $model, \Closure $callQueryBuilder = null, string $alias = null): PaginationBuilderInterface
    {
        $this->model = $model;

        if($alias) {
            $this->alias = $alias;
        }

        $this->queryBuilder = $this->doctrine->getRepository($this->model)->createQueryBuilder($this->alias);

        if(is_callable($callQueryBuilder)) {
            $this->queryBuilder = $callQueryBuilder($this->queryBuilder);
        }

        return $this;
    }

    private function getCursorId(): ?int
    {
        $queryBuilder = $this->doctrine->getRepository($this->model)->createQueryBuilder($this->alias);

        $object = $queryBuilder->where('o.uuid = :uuid')
            ->setParameter('uuid', $this->cursor, 'uuid_binary')
            ->getQuery()
            ->getScalarResult()
        ;

        if($object && is_object($object)) {
            return $object->getId();
        } else if($object && isset($object[0]['o_id'])) {
            return $object[0]['o_id'];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults($type = 'default'): PaginatorInterface
    {
        if($this->countRows) {
            $queryBuilder = clone $this->queryBuilder; // magic happens in here
            try {
                $rows = $queryBuilder->select('count('.$this->alias.'.id)')->getQuery()->getSingleScalarResult();
            } catch (NonUniqueResultException $e) {
                $rows = count($queryBuilder->select('count('.$this->alias.'.id)')->getQuery()->getScalarResult());
            }
            $this->paginator->setNumberOfRows($rows);
            $this->paginator->setLastPage(ceil($rows/$this->limit));
        }

        $queryBuilder = $this->queryBuilder;
        $index = 0;
        $this->orderProperties->map(function (OrderProperty $property) use (&$queryBuilder, &$index) {
            $column = preg_match('/\./', $property->getColumn()) ? $property->getColumn() : $property->getColumn();
            $method = $index++ === 0 ? 'orderBy' : 'addOrderBy';
            $queryBuilder = $queryBuilder->{$method}($column, $property->getDirection());
        });

        $offset = ($this->page-1)*$this->limit;

        if($type === 'cursor') {
            if($this->cursor) {
                $methodWhere = preg_match('/WHERE/', $queryBuilder->getDQL()) ? 'andWhere' : 'where';

                $queryBuilder
                    ->{$methodWhere}('o.id <= :cursor')
                    ->setParameter('cursor', $this->getCursorId())
                ;
            }

            $results = $queryBuilder->getQuery()->getResult();

            // make sure nested data are flat objects
            if(is_array($results[0])) {
                $results = array_map(function ($arr) {
                    return array_first($arr);
                }, $results);
            }

            $data = array_slice($results, 0, $this->limit);

            if(count($results) > $this->limit) {
                $cursor = array_last($results);
                $this->paginator->setNextCursor($cursor->getUuid());
            }

        } else {
            $queryBuilder->setFirstResult($offset)->setMaxResults($this->limit);
            $data = $queryBuilder->getQuery()->getResult();
        }

        $this->paginator->setData($data);

        return $this->paginator;
    }
}
