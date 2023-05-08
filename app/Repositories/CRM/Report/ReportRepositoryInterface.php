<?php

namespace App\Repositories\CRM\Report;

use App\Repositories\Repository;

/**
 * Interface ReportRepositoryInterface
 * @package App\Repositories\CRM\Report
 */
interface ReportRepositoryInterface extends Repository {

    const LEAD_SOURCE_TRAILERTRADERS = 'trailertraders';
    const INVENTORY_ATTRIBUTE_PULL_TYPE_CODE = 'pull_type';
}