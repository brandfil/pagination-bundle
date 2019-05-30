<?php

namespace Brandfil\BrandfilPaginationBundle\Builder;


use Brandfil\BrandfilPaginationBundle\Model\Database\OrderProperty;
use Brandfil\BrandfilPaginationBundle\Model\PaginatorInterface;

/**
 * Interface PaginationBuilderInterface
 * @package Brandfil\BrandfilPaginationBundle\Builder
 */
interface PaginationBuilderInterface
{
    /**
     * @param int $limit
     * @return PaginationBuilderInterface
     */
    public function limit(int $limit): PaginationBuilderInterface;

    /**
     * @param int $page
     * @return PaginationBuilderInterface
     */
    public function page(int $page): PaginationBuilderInterface;

    /**
     * @param bool $count
     * @return PaginationBuilderInterface
     */
    public function countRows(bool $count = false): PaginationBuilderInterface;

    /**
     * @param string $model
     * @param \Closure|null $callQueryBuilder
     * @param string|null $alias
     * @return PaginationBuilderInterface
     */
    public function model(string $model, \Closure $callQueryBuilder = null, string $alias = null): PaginationBuilderInterface;

    /**
     * @param string $property
     * @return PaginationBuilderInterface
     */
    public function registerPropertyName(string $property): PaginationBuilderInterface;

    /**
     * @param OrderProperty $property
     * @return PaginationBuilderInterface
     */
    public function orderBy(OrderProperty $property): PaginationBuilderInterface;

    /**
     * @return PaginatorInterface
     */
    public function getResults(): PaginatorInterface;

    /**
     * @param string|null $cursor
     * @return PaginationBuilderInterface
     */
    public function setCursor(string $cursor = null): PaginationBuilderInterface;
}
