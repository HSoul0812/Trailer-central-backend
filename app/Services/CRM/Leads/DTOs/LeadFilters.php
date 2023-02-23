<?php

namespace App\Services\CRM\Leads\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class LeadFilters
 *
 * @package App\Services\CRM\Leads\DTOs
 */
class LeadFilters
{
    use WithConstructor, WithGetter;

    /**
     * @var array{key: value}
     */
    private $sorts;

    /**
     * @var array{key: value}
     */
    private $archived;

    /**
     * @var array{key: value}
     */
    private $popular;
}
