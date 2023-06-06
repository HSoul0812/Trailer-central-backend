<?php

namespace App\Transformers\CRM\Email;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Email\Campaign;
use App\Transformers\CRM\Email\CampaignReportTransformer;

class CampaignTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'report'
    ];

    public function transform(Campaign $campaign)
    {
	 return [
            'id' => (int)$campaign->drip_campaigns_id,
            'template_id' => (int)$campaign->email_template_id,
            'template' => $campaign->template,
            'location_id' => (int)$campaign->location_id,
            'location' => $campaign->location,
            'send_after_days' => (int)$campaign->send_after_days,
            'action' => $campaign->action,
            'unit_category' => $campaign->unit_category,
            'campaign_name' => $campaign->campaign_name,
            'user_id' => (int)$campaign->user_id,
            'from_email_address' => $campaign->from_email_address,
            'campaign_subject' => $campaign->campaign_subject,
            'include_archived' => (int)$campaign->include_archived,
            'is_enabled' => (int)$campaign->is_enabled,
            'categories' => $campaign->categories,
            'brands' => $campaign->brands,
            'factory_campaign_id' => $campaign->factory ? $campaign->factory->id : null,
            'approved' => $campaign->factory ? $campaign->factory->is_approved : true,
            'is_from_factory' => isset($campaign->factory)
         ];
    }

    public function includeReport(Campaign $campaign)
    {
        return $this->item($campaign->stats, new CampaignReportTransformer());
    }
}
