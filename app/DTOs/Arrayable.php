<?php

namespace App\DTOs;

trait Arrayable
{
    public function toArray(): array
    {
        $vars = get_object_vars ( $this );
        $array = array ();
        foreach ( $vars as $key => $value ) {
            if($value instanceof \Illuminate\Contracts\Support\Arrayable) {
                $array[$key] = $value->toArray();
            } else {
                $array[$key] = $value;
            }
        }
        return $array;
    }
}
