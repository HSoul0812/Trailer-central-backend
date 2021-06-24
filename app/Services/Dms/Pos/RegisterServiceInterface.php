<?php

namespace App\Services\Dms\Pos;

use App\Exceptions\Dms\Pos\RegisterException;

interface RegisterServiceInterface
{
    /**
     * Validates and opens register for given outlet
     *
     * @param array $params
     * @return bool|null
     * @throws RegisterException
     */
    public function open(array $params): ?bool;
}
