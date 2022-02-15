<?php

namespace App\Transformers\CRM\Leads;

use App\Models\CRM\Leads\LeadSource;
use League\Fractal\TransformerAbstract;

class SourceTransformer extends TransformerAbstract {
    /**
     * Transform Lead Source
     * 
     * @param LeadSource $source
     * @return array
     */
    public function transform(LeadSource $source)
    {
        return [
            'lead_source_id' => $source->lead_source_id,
            'user_id' => $source->user_id,
            'source_name' => $source->source_name,
            'date_added' => $source->date_added,
            'parent_id' => $source->parent_id,
            'deleted' => $source->deleted
        ];
    }
}