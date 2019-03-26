<?php

namespace Brandfil\BrandfilPaginationBundle\Model\Database;

/**
 * Class OrderProperty
 * @package Brandfil\BrandfilPaginationBundle\Model\Database
 */
class OrderProperty implements OrderPropertyInterface
{
    /**
     * @var string
     */
    private $column;

    /**
     * @var string
     */
    private $direction;

    /**
     * @var string
     */
    private $entity;


    /**
     * OrderProperty constructor.
     * @param string $column
     * @param string $direction
     * @param null $entity
     */
    public function __construct(string $column, string $direction, $entity = null)
    {
        $this->column = $column;
        $this->direction = $direction;
        $this->entity = $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * {@inheritdoc}
     */
    public function getDirection(): string
    {
        return $this->direction;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity(): string
    {
        return $this->entity;
    }
}