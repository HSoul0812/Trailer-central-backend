<?php

namespace App\Transformers\CRM\Text;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Interactions\TextLog;

class TextTransformer extends TransformerAbstract
{
    public function transform(TextLog $text)
    {
	 return [
             'id' => (int)$text->id,
             'lead_id' => (int)$text->lead_id,
             'log_message' => $text->log_message,
             'from_number' => $text->from_number,
             'to_number' => $text->to_number,
             'date_sent' => $text->date_sent,
         ];
    }
}