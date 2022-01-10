<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Http\Controllers\AbstractRestfulController;
use Dingo\Api\Http\Response;

class InventoryController extends AbstractRestfulController
{
    public function __construct(private InventoryServiceInterface $inventoryService)
    {
        parent::__construct();
    }

    public function list(): Response {

    }
}
