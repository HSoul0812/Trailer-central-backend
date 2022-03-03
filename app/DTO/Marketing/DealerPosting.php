<?php

namespace App\DTO\Marketing;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class DealerPosting
 * 
 * @package App\DTO\Marketing
 */
class DealerPosting
{
    use WithConstructor, WithGetter;

    /**
     * @var int
     */
    private $dealerId;

    /**
     * @var int
     */
    private $integrationId;

    /**
     * @var int
     */
    private $expiryTime;
}