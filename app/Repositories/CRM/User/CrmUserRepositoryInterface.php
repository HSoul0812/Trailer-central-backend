<?php

namespace App\Repositories\CRM\User;

use App\Repositories\Repository;

/**
 * Interface CrmUserInterface
 * @package App\Repositories\User
 */
interface CrmUserRepositoryInterface extends Repository {

    /**
     * Fields Name of CrmUser Settings
     */
    const SETTING_FIELDS = [
        'price_per_mile',
        'email_signature',
        'timezone',
        'enable_hot_potato',
        'disable_daily_digest',
        'enable_assign_notification',
        'enable_due_notification',
        'enable_past_notification'
    ];
}
