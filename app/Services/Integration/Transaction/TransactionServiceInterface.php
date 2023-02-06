<?php

namespace App\Services\Integration\Transaction;

/**
 * Class TransactionServiceInterface
 * @package App\Services\Integration\Transaction
 */
interface TransactionServiceInterface
{
    /**
     * @param array $params
     * @return string
     */
    public function post(array $params): string;
}
