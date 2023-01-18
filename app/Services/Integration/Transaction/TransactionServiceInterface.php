<?php

namespace App\Services\Integration\Transaction;

/**
 * Class TransactionServiceInterface
 * @package App\Services\Integration\Transaction
 */
interface TransactionServiceInterface
{
    public function post(array $params): array;
}
