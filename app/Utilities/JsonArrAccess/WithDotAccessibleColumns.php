<?php


namespace App\Utilities\JsonArrAccess;


use Illuminate\Support\Arr;

trait WithDotAccessibleColumns
{
    public function getByName($column, $name)
    {
        return Arr::get($this->$column, $name);
    }

    public function setByName($column, $name, $value)
    {
        $array = $this->$column;
        Arr::set($array, $name, $value);

        $this->$column = $array;
    }
}
