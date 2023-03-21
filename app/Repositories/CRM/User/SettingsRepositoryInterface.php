<?php

namespace App\Repositories\CRM\User;

use App\Models\CRM\User\Settings;
use App\Repositories\Repository;
use Illuminate\Support\Collection;

/**
 * Interface SettingsRepositoryInterface
 * 
 * @package App\Repositories\CRM\User
 */
interface SettingsRepositoryInterface extends Repository {

    const FILTER_SORT = 'default/filters/sort';
    const HOT_POTATO_DELAY = 'round-robin/hot-potato/delay';
    const HOT_POTATO_DURATION = 'round-robin/hot-potato/duration';
    const HOT_POTATO_END_HOUR = 'round-robin/hot-potato/end-hour';
    const HOT_POTATO_SKIP_WEEKENDS = 'round-robin/hot-potato/skip-weekends';
    const HOT_POTATO_START_HOUR = 'round-robin/hot-potato/start-hour';
    const HOT_POTATO_USE_SUBMISSION_DATE = 'round-robin/hot-potato/use-submission-date';
    
    const SETTING_FIELDS = [
        self::FILTER_SORT,
        self::HOT_POTATO_DELAY,
        self::HOT_POTATO_DURATION,
        self::HOT_POTATO_END_HOUR,
        self::HOT_POTATO_SKIP_WEEKENDS,
        self::HOT_POTATO_START_HOUR,
        self::HOT_POTATO_USE_SUBMISSION_DATE
    ];

    /**
     * Get All CRM Settings By Dealer
     * 
     * @param int $dealerId
     * @return Collection<Settings>
     */
    public function getByDealer(int $dealerId): Collection;
}
