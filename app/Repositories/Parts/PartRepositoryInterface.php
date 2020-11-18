<?php

namespace App\Repositories\Parts;

use \App\Repositories\Repository;

/**
 *
 *
 * @author Eczek
 */
interface PartRepositoryInterface extends Repository {

    public function getBySku($sku);

    public function getDealerSku($dealerId, $sku);

    public function getAllByDealerId($dealerId);

    public function search($query, $dealerId, $allowAll = false, &$paginator = null);

}
