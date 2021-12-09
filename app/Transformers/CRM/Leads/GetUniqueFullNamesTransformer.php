<?php

namespace App\Transformers\CRM\Leads;

use League\Fractal\TransformerAbstract;

/**
 * Class GetUniqueFullNamesTransformer
 * @package App\Transformers\CRM\Leads
 */
class GetUniqueFullNamesTransformer extends TransformerAbstract
{
    /**
     * @param \stdClass $data
     * @return array
     */
    public function transform(\stdClass $data): array
    {
        return [
            'full_name' => $data->first_name . ' ' . $data->last_name,
            'first_name' => $data->first_name,
            'last_name' => $data->last_name,
        ];
    }
}
