<?php

namespace App\Services\Parts;

use App\Models\Parts\Part;

/**
 *
 * @author Eczek
 */
interface PartServiceInterface {
    
    /**
     * Creates a part
     * 
     * @param array $partData
     */
    public function create($partData, $bins) : Part;
    
    /**
     * Updates a part
     * 
     * @param array $partData
     */
    public function update($partData, $bins) : Part;
    
}
