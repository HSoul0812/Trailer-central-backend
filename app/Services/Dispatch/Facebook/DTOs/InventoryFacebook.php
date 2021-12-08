<?php

namespace App\Services\Dispatch\Facebook\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class InventoryFacebook
 * 
 * @package App\Services\Dispatch\Facebook\DTOs
 */
class InventoryFacebook
{
    use WithConstructor, WithGetter;

    /**
     * @var int
     */
    private $inventoryId;

    /**
     * @var int
     */
    private $facebookId;
}