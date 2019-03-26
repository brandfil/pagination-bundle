<?php

namespace Brandfil\BrandfilPaginationBundle\Model\Database;

/**
 * Interface OrderPropertyInterface
 * @package Brandfil\BrandfilPaginationBundle\Model\Database
 */
interface OrderPropertyInterface
{
    public const ASC = 'ASC';
    public const DESC = 'DESC';

    /**
     * @return string
     */
    public function getColumn(): string;

    /**
     * @return string
     */
    public function getDirection(): string;

    /**
     * @return string
     */
    public function getEntity(): string;
}