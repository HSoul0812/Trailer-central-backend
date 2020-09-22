<?php

namespace App\Transformers\Website\Forms;

use League\Fractal\TransformerAbstract;
use App\Models\Website\Forms\FieldMap;

class FieldMapTransformer extends TransformerAbstract
{
    public function transform(FieldMap $fieldMap)
    {
        return [
            $fieldMap->form_field => [
               'id' => (int)$fieldMap->id,
               'map' => $fieldMap->map_field,
               'db' => $fieldMap->db_table,
               'created_at' => $post->created_at,
               'update_at' => $post->update_at,
            ]
        ];
    }
}
