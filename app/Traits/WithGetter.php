<?php

declare(strict_types=1);

namespace App\Traits;

use App\Exceptions\PropertyDoesNotExists;

trait WithGetter
{
    /**
     * @param  string  $key
     * @return mixed
     * @throws PropertyDoesNotExists when the desired property does not exists
     */
    public function __get(string $key)
    {
        if(property_exists($this, $key)){
            return $this->{$key};
        }

        throw new PropertyDoesNotExists("'$key' property does not exists");
    }

    /**
     * @param string $key
     * @return bool
     */
    public function __isset(string $key)
    {
        return isset($this->{$key});
    }
}
