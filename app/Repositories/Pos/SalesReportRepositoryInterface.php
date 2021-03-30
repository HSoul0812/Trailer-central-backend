<?php

declare(strict_types=1);

namespace App\Repositories\Pos;

use App\Repositories\GenericRepository;
use Generator;

/**
 * Describes the repository for Sales Reports
 */
interface SalesReportRepositoryInterface extends GenericRepository
{
    /**
     * Provides the data for the report
     *
     * @param array $params
     * @return array
     */
    public function customReport(array $params): array;

    /**
     * Provides a cursor to be iterate
     *
     * @param array $params
     * @return array
     */
    public function customReportCursor(array $params): Generator;
}
