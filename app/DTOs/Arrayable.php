<?php

namespace App\DTOs;

function get_object_public_vars($object)
{
    return get_object_vars($object);
}

trait Arrayable
{
    public function toArray(): array
    {
        $vars = get_object_public_vars($this);
        $array = [];
        foreach ($vars as $key => $value) {
            if ($value instanceof \Illuminate\Contracts\Support\Arrayable) {
                $array[$key] = $value->toArray();
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }
}
