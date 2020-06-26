<?php

namespace App\Repositories\Dms;

use \App\Repositories\Repository;

/**
 * @author Marcel
 */
interface QuoteRepositoryInterface extends Repository {
    /**
     * Get totals of records filtered by $params
     *
     * @param array $params
     */
    public function getTotals($params);
}
