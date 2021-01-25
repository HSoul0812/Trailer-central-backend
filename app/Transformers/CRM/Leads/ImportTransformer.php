<?php

namespace App\Transformers\CRM\Leads;

use App\Models\CRM\Leads\LeadImport;
use League\Fractal\TransformerAbstract;

class ImportTransformer extends TransformerAbstract {

    /**
     * Transform Lead Import
     * 
     * @param LeadImport $import
     * @return array
     */
    public function transform(LeadImport $import)
    {        
        return [
            'id' => $import->id,
            'dealer_id' => $import->dealer_id,
            'email' => $import->email,
            'created_at' => $import->created_at,
            'updated_at' => $import->updated_at
        ];
    }
}