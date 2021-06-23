<?php

declare(strict_types=1);

namespace App\Services\Dms\Pos;

interface RegisterServiceInterface
{
    /**
     * Validates and opens register for given outlet
     *
     * @param array $params
     * @return bool
     */
    public function open(array $params): bool;
}
