<?php

namespace App\Transformers\CRM\Text;

use App\Models\CRM\Text\NumberVerify;
use League\Fractal\TransformerAbstract;

class NumberVerifyTransformer extends TransformerAbstract
{
    public function transform(NumberVerify $verify)
    {
        return [
            'id' => (int) $verify->id,
            'dealer_number' => $verify->dealer_number,
            'twilio_number' => $verify->twilio_number,
            'verify_type' => $verify->verify_type,
            'created_at' => $verify->created_at,
            'updated_at' => $verify->updated_at
        ];
    }
}
