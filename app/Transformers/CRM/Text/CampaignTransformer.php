<?php

namespace App\Transformers\CRM\Text;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Text\Campaign;
use App\Transformers\CRM\Leads\LeadTransformer;

class CampaignTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'leads',
        'report'
    ];

    public function transform(Campaign $campaign)
    {
	 return [
             'id' => (int)$campaign->id,
             'user_id' => (int)$campaign->user_id,
             'template_id' => (int)$campaign->template_id,
             'template' => $campaign->template,
             'campaign_name' => $campaign->campaign_name,
             'from_sms_number' => $campaign->from_sms_number,
             'action' => $campaign->action,
             'location_id' => (int)$campaign->location_id,
             'send_after_days' => (int)$campaign->send_after_days,
             'categories' => $campaign->categories,
             'brands' => $campaign->brands,
             'include_archived' => (int)$campaign->include_archived,
             'is_enabled' => (int)$campaign->is_enabled,
             'created_at' => $campaign->created_at,
             'updated_at' => $campaign->updated_at,
             'deleted' => (int)$campaign->deleted,
         ];
    }

    public function includeLeads(Campaign $campaign)
    {
        return $this->collection($campaign->leads, new LeadTransformer());
    }

    public function includeReport(Campaign $campaign)
    {
        return $this->item($campaign->stats, new CampaignReportTransformer());
    }
}
