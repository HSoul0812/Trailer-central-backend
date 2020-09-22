<?php

namespace App\Transformers\Website\Forms;

use League\Fractal\TransformerAbstract;
use App\Models\Website\Forms\FieldMap;

class FieldMapTransformer extends TransformerAbstract
{
    public function transform(FieldMap $fieldMap)
    {
        return [
            'id'         => (int)$fieldMap->id,
            'type'       => $fieldMap->type,
            'field'      => $fieldMap->form_field,
            'map'        => $fieldMap->map_field,
            'db'         => $fieldMap->db_table,
            'created_at' => $fieldMap->created_at,
            'updated_at' => $fieldMap->updated_at
        ];
    }
}
