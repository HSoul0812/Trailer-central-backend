<?php

namespace App\Http\Requests;

interface RequestInterface
{
    /**
     * @throws ResourceException when there were some validation error
     */
    public function validate(): bool;
}
