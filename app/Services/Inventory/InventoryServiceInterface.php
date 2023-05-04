<?php

namespace App\Services\Inventory;

use App\DTOs\Inventory\TcApiResponseInventory;
use App\DTOs\Inventory\TcApiResponseInventoryCreate;
use App\DTOs\Inventory\TcApiResponseInventoryDelete;
use App\DTOs\Inventory\TcEsResponseInventoryList;
use Illuminate\Support\Collection;

interface InventoryServiceInterface
{
    public function list(array $params): TcEsResponseInventoryList;

    public function show(int $id): TcApiResponseInventory;

    public function attributes(array $params): Collection;

    public function create(int $userId, array $params): TcApiResponseInventoryCreate;

    public function update(int $userId, array $params): TcApiResponseInventoryCreate;

    public function delete(int $userId, int $id): TcApiResponseInventoryDelete;

    public function hideExpired();
}
