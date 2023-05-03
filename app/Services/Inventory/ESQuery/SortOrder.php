<?php

namespace App\Services\Inventory\ESQuery;

class SortOrder
{
    public const ORDER_DESC = 'desc';
    public const ORDER_ASC = 'asc';

    private string $direction;
    private string $field;

    public function __construct(string $sort)
    {
        if ($sort[0] === '+') {
            $this->direction = self::ORDER_ASC;
            $this->field = substr($sort, 1);
        } elseif ($sort[0] === '-') {
            $this->direction = self::ORDER_DESC;
            $this->field = substr($sort, 1);
        } else {
            $this->direction = self::ORDER_ASC;
            $this->field = $sort;
        }
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}
