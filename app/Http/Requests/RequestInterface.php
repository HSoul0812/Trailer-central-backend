<?php

namespace App\Http\Requests;

interface RequestInterface
{
    /**
     * @throws \Dingo\Api\Exception\ResourceException when there were some validation error
     */
    public function validate(): bool;

    public function all($keys = null);
}
