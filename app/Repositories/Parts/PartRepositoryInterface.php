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
    
}
