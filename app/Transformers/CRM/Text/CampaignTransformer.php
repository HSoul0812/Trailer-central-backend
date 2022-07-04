<?php

namespace App\Transformers\CRM\Text;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Text\Campaign;
use App\Transformers\CRM\Leads\LeadTransformer;
use App\Transformers\CRM\Text\CampaignReportTransformer;

class CampaignTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'leads',
        'report'
    ];

    /**
     * @var LeadTransformer
     */
    private $leadTransformer;

    /**
     * @var CampaignReportTransformer
     */
    private $reportTransformer;

    /**
     * CampaignTransformer constructor.
     *
     * @param LeadTransformer $leadTransformer
     * @param CampaignReportTransformer $reportTransformer
     */
    public function __construct(
        LeadTransformer $leadTransformer,
        CampaignReportTransformer $reportTransformer
    ) {
        $this->leadTransformer = $leadTransformer;
        $this->reportTransformer = $reportTransformer;
    }

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
            'is_error' => (int)$campaign->is_error,
            'created_at' => $campaign->created_at,
            'updated_at' => $campaign->updated_at,
            'deleted' => (int)$campaign->deleted,
            'log' => $campaign->log,
        ];
    }

    public function includeLeads(Campaign $campaign)
    {
        return $this->collection($campaign->leads, $this->leadTransformer);
    }

    public function includeReport(Campaign $campaign)
    {
        return $this->item($campaign->stats, $this->reportTransformer);
    }
}
