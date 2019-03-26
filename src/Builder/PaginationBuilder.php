<?php

namespace Brandfil\BrandfilPaginationBundle\Builder;

use Brandfil\BrandfilPaginationBundle\Model\Database\OrderProperty;
use Brandfil\BrandfilPaginationBundle\Model\PaginatorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;
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
    public function model(string $model, \Closure $callQueryBuilder = null, string $alias = null): PaginationBuilderInterface
    {
        if($alias) {
            $this->alias = $alias;
        }

        $this->queryBuilder = $this->doctrine->getRepository($model)->createQueryBuilder($this->alias);

        if(is_callable($callQueryBuilder)) {
            $this->queryBuilder = $callQueryBuilder($this->queryBuilder);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults(): PaginatorInterface
    {
        if($this->countRows) {
            $queryBuilder = clone $this->queryBuilder; // magic happens in here
            $rows = $queryBuilder->select('count('.$this->alias.'.id)')->getQuery()->getSingleScalarResult();
            $this->paginator->setNumberOfRows($rows);
            $this->paginator->setLastPage(ceil($rows/$this->limit));
        }

        $queryBuilder = $this->queryBuilder;
        $index = 0;
        $this->orderProperties->map(function (OrderProperty $property) use (&$queryBuilder, &$index) {
            $column = preg_match('/\./', $property->getColumn()) ? $property->getColumn() : $this->alias.'.'.$property->getColumn();
            $method = $index++ === 0 ? 'orderBy' : 'addOrderBy';
            $queryBuilder->{$method}($column, $property->getDirection());
        });

        $offset = ($this->page-1)*$this->limit;
        $queryBuilder->setFirstResult($offset)->setMaxResults($this->limit);

        $this->paginator->setData($queryBuilder->getQuery()->getResult());

        return $this->paginator;
    }
}