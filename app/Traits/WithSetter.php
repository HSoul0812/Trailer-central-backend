<?php

declare(strict_types=1);

namespace App\Traits;

use App\Exceptions\PropertyDoesNotExists;

trait WithSetter
{
    /**
     * @param  string  $key
     * @param  mixed   $value
     * @return mixed
     * @throws PropertyDoesNotExists when the desired property does not exists
     */
    public function __set(string $key, $value)
    {
        if(property_exists($this, $key)){
            $this->{$key} = $value;
        } else {
            throw new PropertyDoesNotExists("'$key' property does not exists");
        }
    }
}
