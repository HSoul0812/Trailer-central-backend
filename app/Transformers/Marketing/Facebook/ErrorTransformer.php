<?php

namespace App\Transformers\Marketing\Facebook;

use App\Models\Marketing\Facebook\Error;
use League\Fractal\TransformerAbstract;

class ErrorTransformer extends TransformerAbstract
{
    public function transform(Error $error)
    {
        // Return Array
        return [
            'id' => $error->id,
            'marketplace_id' => $error->marketplace_id,
            'inventory_id' => $error->inventory_id,
            'action' => $error->action,
            'step' => $error->step,
            'type' => $error->error_type,
            'desc' => $error->error_desc,
            'message' => $error->error_message,
            'dismissed' => $error->dismissed,
            'expires_at' => $error->expires_at,
            'created_at' => $error->created_at,
            'updated_at' => $error->updated_at
        ];
    }
}
