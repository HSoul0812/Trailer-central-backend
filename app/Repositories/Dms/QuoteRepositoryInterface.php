<?php

namespace App\Repositories\Dms;

use \App\Repositories\Repository;
use Illuminate\Database\Eloquent\Collection;

/**
 * @author Marcel
 */
interface QuoteRepositoryInterface extends Repository
{
    /**
     * Get totals of records filtered by $params
     *
     * @param array $params
     */
    public function getTotals($params);

    public function getCompletedDeals(int $dealerId): Collection;

    /**
     * @param int $dealerId
     * @param array $quoteIds
     *
     * @return bool
     */
    public function bulkArchive(int $dealerId, array $quoteIds): bool;
}
