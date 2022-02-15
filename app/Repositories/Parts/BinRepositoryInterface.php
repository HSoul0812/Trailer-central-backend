<?php

namespace App\Repositories\Parts;

use \App\Repositories\Repository;

/**
 *
 *
 * @author David A Conway Jr
 */
interface BinRepositoryInterface extends Repository {

    public function financialReportByDealer(int $dealerId): array;
}
