<?php

namespace App\Transformers\CRM\Text;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Text\Stop;

class StopTransformer extends TransformerAbstract
{
    public function transform(Stop $stop)
    {
	 return [
             'id' => (int)$stop->id,
             'lead_id' => (int)$stop->lead_id,
             'text_id' => (int)$stop->text_id,
             'sms_number' => $stop->sms_number,
             'created_at' => $stop->created_at,
             'updated_at' => $stop->updated_at,
         ];
    }
}
