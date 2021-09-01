<?php

namespace App\Http\Requests;

interface RequestInterface 
{
    /**
     * @return bool
     *
     * @throws ResourceException when there were some validation error
     */
    public function validate(): bool;
}
