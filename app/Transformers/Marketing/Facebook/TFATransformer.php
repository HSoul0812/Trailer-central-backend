<?php

namespace App\Transformers\Marketing\Facebook;

use App\Services\Marketing\Facebook\DTOs\TfaType;
use League\Fractal\TransformerAbstract;

class TFATransformer extends TransformerAbstract
{
    public function transform(TfaType $type)
    {
        // Return Array
        return [
            'code' => $type->code,
            'name' => $type->name,
            'fields' => $type->getFields(),
            'autocomplete' => $type->getAutocomplete(),
            'note' => $type->getNote()
        ];
    }
}
