<?php

namespace App\Http\Controllers\v1\Inventory\Floorplan;

use App\Http\Controllers\v1\Parts\VendorController as PartsVendorController;
use App\Repositories\Inventory\Floorplan\VendorRepositoryInterface;

class VendorController extends PartsVendorController
{
    protected $vendors;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(VendorRepositoryInterface $vendors)
    {
        $this->vendors = $vendors;
    }
    
}
