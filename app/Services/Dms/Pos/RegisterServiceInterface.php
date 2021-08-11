<?php

namespace App\Services\Dms\Pos;

use App\Exceptions\Dms\Pos\RegisterException;

interface RegisterServiceInterface
{
    /**
     * Validates and opens register for given outlet
     *
     * @param array $params
     * @return string
     * @throws RegisterException
     */
    public function open(array $params): string;
}
