<?php

namespace App\Services\Common\DTOs;

/**
 * Class SimpleData
 * 
 * @package App\Services\Common\DTOs
 */
class SimpleData
{
    /**
     * @var string Index of Entry
     */
    private $index;

    /**
     * @var string Name of Entry
     */
    private $name;


    /**
     * Return Index
     * 
     * @return string $this->index
     */
    public function getIndex(): string
    {
        return $this->index;
    }

    /**
     * Set Index
     * 
     * @param string $index
     * @return void
     */
    public function setIndex(string $index): void
    {
        $this->index = $index;
    }


    /**
     * Return Name
     * 
     * @return string $this->name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set Name
     * 
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}