<?php

namespace App\Transformers\Dms\Printer;

use App\Models\CRM\Dms\Printer\Form;
use League\Fractal\TransformerAbstract;

class FormTransformer extends TransformerAbstract
{

    public function transform(Form $form)
    {
        return [
            'id' => (int) $form->id,
            'name' => $form->name,
            'type' => $form->type,
            'region' => $form->region,
            'region_name' => $form->regionCode->region_name ?? '',
            'label' => $form->label,
            'description' => $form->description,
            'department' => $form->department,
            'division' => $form->division,
            'website' => $form->website,
            'created_at' => $form->created_at,
            'updated_at' => $form->updated_at
        ];
    }
}
