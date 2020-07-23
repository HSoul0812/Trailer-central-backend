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
             'response_id' => (int)$stop->response_id,
             'text_number' => $stop->text_number,
             'created_at' => $stop->created_at,
             'updated_at' => $stop->updated_at,
         ];
    }
}
