<?php

namespace App\Repositories\Dms\Quickbooks;

use \App\Repositories\Repository;

/**
 * @author Marcel
 */
interface ExpenseRepositoryInterface extends Repository
{
    /**
     * @param int $dealerId
     * @param string $checkNumber
     * @param array $params
     *
     * @return bool
     */
    public function checkNumberExists(int $dealerId, string $checkNumber, array $params = []): bool;
}
