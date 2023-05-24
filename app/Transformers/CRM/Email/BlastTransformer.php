<?php

namespace App\Transformers\CRM\Email;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Email\Blast;

class BlastTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'report'
    ];

    public function transform(Blast $blast)
    {
        return [
            'id' => (int)$blast->email_blasts_id,
            'template_id' => (int)$blast->email_template_id,
            'location_id' => $blast->location_id,
            'location' => $blast->location,
            'send_after_days' => (int)$blast->send_after_days,
            'action' => strtoupper($blast->action),
            'unit_category' => $blast->unit_category,
            'categories' => $blast->categories,
            'brands' => $blast->brands,
            'campaign_name' => $blast->campaign_name,
            'user_id' => (int)$blast->user_id,
            'from_email_address' => $blast->from_email_address,
            'campaign_subject' => $blast->campaign_subject,
            'include_archived' => $blast->include_archived,
            'send_date' => $blast->send_date,
            'delivered' => $blast->delivered,
            'cancelled' => $blast->cancelled,
            'total_sent' => $blast->sents()->count(),
            'factory_campaign_id' => $blast->factory ? $blast->factory->id : null,
            'approved' => $blast->factory ? $blast->factory->is_approved : true,
        ];
    }

    public function includeReport(Blast $blast)
    {
        return $this->item($blast->stats, new BlastReportTransformer());
    }
}
