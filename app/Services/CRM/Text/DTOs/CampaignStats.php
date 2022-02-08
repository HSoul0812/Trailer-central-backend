<?php

namespace App\Services\CRM\Text\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;


/**
 * Class CampaignStats
 * 
 * @package App\Services\CRM\Text\DTOs
 */
class CampaignStats
{
    use WithConstructor, WithGetter;

    /**
     * @var int Number of Skipped
     */
    protected $skipped;

    /**
     * @var int Number of Sent Successfully
     */
    protected $sent;

    /**
     * @var int Number of Failed
     */
    protected $failed;

    /**
     * @var int Number of Unsubscribed
     */
    protected $unsubscribed;
}