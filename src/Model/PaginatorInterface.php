<?php

namespace Brandfil\BrandfilPaginationBundle\Model;

/**
 * Interface PaginatorInterface
 * @package Brandfil\BrandfilPaginationBundle\Model
 */
interface PaginatorInterface
{
    /**
     * @param int $number
     * @return PaginatorInterface
     */
    public function setNumberOfRows(int $number): PaginatorInterface;

    /**
     * @param int $page
     * @return PaginatorInterface
     */
    public function setLastPage(int $page): PaginatorInterface;

    /**
     * @param int $page
     * @return PaginatorInterface
     */
    public function setCurrentPage(int $page): PaginatorInterface;

    /**
     * @param mixed $data
     * @return PaginatorInterface
     */
    public function setData($data): PaginatorInterface;
}