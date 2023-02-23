<?php

namespace App\Transformers\CRM\Leads;

use League\Fractal\TransformerAbstract;

/**
 * Class LeadFilterTransformer
 * @package App\Transformers\CRM\Leads
 */
class LeadFiltersTransformer extends TransformerAbstract
{
    /**
     * @param LeadFilters $filters
     * @return array
     */
    public function transform(LeadFilters $filters): array
    {
        // Initialize Filters Response
        $response = [
            'sorts' => $filters->sorts,
            'archived' => $filters->archived,
            'filters' => []
        ];

        // Get Popular Filters
        foreach($filters->popular as $popular) {
            $response['filters'][] = [
                'label'   => $popular->label,
                'type'    => $popular->type,
                'time'    => $popular->time,
                'filters' => $popular->calculateFilters()
            ];
        }

        // Return Filters
        return $response;
    }
}
