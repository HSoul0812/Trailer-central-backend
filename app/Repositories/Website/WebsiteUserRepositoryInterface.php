<?php


namespace App\Repositories\Website;

use App\Repositories\Repository;

/**
 * Interface WebsiteRepositoryInterface
 * @package App\Repositories\Website
 */
interface WebsiteUserRepositoryInterface extends Repository
{
    const DEFAULT_GET_PARAMS = [
        self::CONDITION_AND_WHERE => [
            ['is_active', '=', 1],
        ]
    ];
}
