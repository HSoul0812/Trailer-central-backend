<?php

namespace App\Services\CRM\Email\DTOs;

use App\Services\CRM\Email\DTOs\CampaignStats;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class CampaignStats
 * 
 * @package App\Services\CRM\Email\DTOs
 */
class BlastStats extends CampaignStats
{
    use WithConstructor, WithGetter;
}