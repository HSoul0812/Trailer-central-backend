<?php
namespace App\Repositories\Ecommerce;
use App\Repositories\Repository;

interface CompletedOrderRepositoryInterface extends Repository
{
    public function getAll($params);

    public function beginTransaction(): void;

    public function commitTransaction(): void;

    public function rollbackTransaction(): void;
}