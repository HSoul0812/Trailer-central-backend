<?php

declare(strict_types=1);

namespace App\Repositories\Pos;

use App\Repositories\GenericRepository;

/**
 * Describes the repository for Sales Reports
 */
interface SalesReportRepositoryInterface extends GenericRepository
{
    public function customReport(array $params): array;
}
