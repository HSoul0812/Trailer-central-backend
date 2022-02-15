<?php

namespace App\Transformers\Parts;

use League\Fractal\TransformerAbstract;
use App\Models\Parts\Bin;

class BinTransformer extends TransformerAbstract
{
    public function transform($bin)
    {          
        if (empty($bin)) {
            return [];
        }
        
        if (isset($bin->bin)) {
            $bin = $bin->bin;
        }
        
        return [
             'id' => (int)$bin->id,
             'dealer_id' => (int) $bin->dealer_id,
             'name' => $bin->bin_name,
             'uncompletedCycleCounts' => $bin->uncompletedCycleCounts,
             'location_name' => $bin->dealerLocation ? $bin->dealerLocation->name : null
         ];
    }
}