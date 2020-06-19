<?php

namespace App\Repositories\Website;

use App\Repositories\Repository;

/**
 * Interface WebsiteRepositoryInterface
 * @package App\Repositories\Website
 */
interface WebsiteRepositoryInterface extends Repository
{
    const DEFAULT_GET_PARAMS = [
        self::CONDITION_AND_WHERE => [
            ['is_active', '=', 1],
            ['is_live', '=', 1]
        ]
    ];

    public function getAllByConfigParams($params, bool $withDefault = true);

    public function getAll($params, bool $withDefault = true);
}
