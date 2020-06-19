<?php

namespace App\Transformers\CRM\Text;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Text\Blast;

class BlastTransformer extends TransformerAbstract
{
    public function transform(Blast $blast)
    {
	 return [
             'id' => (int)$blast->id,
             'user_id' => (int)$blast->user_id,
             'template_id' => (int)$blast->template_id,
             'campaign_name' => $blast->campaign_name,
             'campaign_subject' => $blast->campaign_subject,
             'from_email_address' => $blast->from_email_address,
             'action' => $blast->action,
             'location_id' => (int)$blast->location_id,
             'send_after_days' => (int)$blast->send_after_days,
             'unit_category' => $blast->unit_category,
             'include_archived' => (int)$blast->include_archived,
             'is_delivered' => (int)$blast->is_delivered,
             'is_cancelled' => (int)$blast->is_cancelled,
             'created_at' => $blast->created_at,
             'updated_at' => $blast->updated_at,
             'deleted' => (int)$blast->deleted,
         ];
    }
}
