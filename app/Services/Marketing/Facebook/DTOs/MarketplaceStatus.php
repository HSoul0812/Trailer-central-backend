<?php

namespace App\Services\Marketing\Facebook\DTOs;

use App\Services\Marketing\Facebook\DTOs\TfaType;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class MarketplaceStatus
 * 
 * @package App\Services\Marketing\Facebook\DTOs
 */
class MarketplaceStatus
{
    use WithConstructor, WithGetter;


    /**
     * @var string
     */
    private $pageUrl;

    /**
     * @var Collection<TfaType>
     */
    private $tfaTypes;
}