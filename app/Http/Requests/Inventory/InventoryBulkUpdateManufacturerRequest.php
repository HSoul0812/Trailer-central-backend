<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;
use App\Repositories\Inventory\InventoryBulkUpdateRepositoryInterface;

class InventoryBulkUpdateManufacturerRequest extends Request
{
    /**
     * @var Manufacturers
     */
    private $manufacturer;

    protected $rules = [
        'from_manufacturer' => 'string|required',
        'to_manufacturer' => 'string|required'
    ];

    protected function getRepository(): InventoryBulkUpdateRepositoryInterface
    {
        return $this->app(InventoryBulkUpdateRepositoryInterface::class);
    }
}
