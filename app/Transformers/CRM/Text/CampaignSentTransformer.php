<?php

namespace App\Transformers\CRM\Text;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Text\CampaignSent;

class CampaignSentTransformer extends TransformerAbstract
{
    public function transform(CampaignSent $sent)
    {
	 return [
             'id' => (int)$sent->id,
             'lead_id' => (int)$sent->lead_id,
             'text_id' => (int)$sent->text_id,
             'created_at' => $sent->created_at,
             'updated_at' => $sent->updated_at,
             'deleted' => (int)$sent->deleted,
         ];
    }
}
