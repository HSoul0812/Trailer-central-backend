<?php

namespace App\Transformers\CRM\Leads;

use League\Fractal\TransformerAbstract;

/**
 * Class LeadFilterTransformer
 * @package App\Transformers\CRM\Leads
 */
class LeadFilterTransformer extends TransformerAbstract
{
    /**
     * @param LeadFilters $filters
     * @return array
     */
    public function transform(LeadFilters $filters): array
    {
        $filters = [];

        // Get Sorts
        $filters['sorts'] = $sorts;

        // Return Filters
        return $filters;
    }
}
