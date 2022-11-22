<?php

namespace App\Service\Marketing\Craigslist\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class ClientValidate
 * 
 * @package App\Service\Marketing\Craigslist\DTOs
 */
class ClientValidate
{
    use WithConstructor, WithGetter;


    /**
     * @var int
     */
    private $dealerId;

    /**
     * @var int
     */
    private $slotId;

    /**
     * @var string
     */
    private $uuid;

    /**
     * @var int
     */
    private $elapsed;

    /**
     * @var string
     */
    private $level;
}