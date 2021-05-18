<?php

namespace App\Transformers\Dms\Printer;

use App\Models\CRM\Dms\Printer\Okidata;
use League\Fractal\TransformerAbstract;

class OkidataTransformer extends TransformerAbstract
{

    public function transform(Okidata $okidata)
    {
        return [
            'id' => (int) $okidata->id,
            'name' => $okidata->name,
            'region' => $okidata->region->region_code,
            'region_name' => $okidata->region_name,
            'description' => $okidata->description,
            'department' => $okidata->department,
            'division' => $okidata->division,
            'website' => $okidata->website,
            'created_at' => $okidata->created_at,
            'updated_at' => $okidata->updated_at
        ];
    }
}
