<?php

namespace App\Services\CRM\Email\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class CampaignStats
 * 
 * @package App\Services\CRM\Email\DTOs
 */
class CampaignStats
{
    use WithConstructor, WithGetter;

    /**
     * @var int Number of Sent Successfully
     */
    protected $sent;

    /**
     * @var int Number of Delivered
     */
    protected $delivered;

    /**
     * @var int Number of Bounced
     */
    protected $bounced;

    /**
     * @var int Number of Complained
     */
    protected $complaints;

    /**
     * @var int Number of Unsubscribed
     */
    protected $unsubscribed;

    /**
     * @var int Number of Opened
     */
    protected $opened;

    /**
     * @var int Number of Clicked
     */
    protected $clicked;

    /**
     * @var int Number of Skipped
     */
    protected $skipped;

    /**
     * @var int Number of Failed
     */
    protected $failed;
}