<?php

namespace App\Transformers\CRM\Text;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Text\Blast;
use App\Transformers\CRM\Leads\LeadTransformer;
use App\Transformers\CRM\Text\BlastReportTransformer;

class BlastTransformer extends TransformerAbstract
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
     * @var BlastReportTransformer
     */
    private $reportTransformer;

    /**
     * BlastTransformer constructor.
     *
     * @param LeadTransformer $leadTransformer
     * @param BlastReportTransformer $reportTransformer
     */
    public function __construct(
        LeadTransformer $leadTransformer,
        BlastReportTransformer $reportTransformer
    ) {
        $this->leadTransformer = $leadTransformer;
        $this->reportTransformer = $reportTransformer;
    }

    public function transform(Blast $blast)
    {
        return [
            'id' => (int)$blast->id,
            'user_id' => (int)$blast->user_id,
            'template_id' => (int)$blast->template_id,
            'template' => $blast->template,
            'campaign_name' => $blast->campaign_name,
            'from_sms_number' => $blast->from_sms_number,
            'action' => $blast->action,
            'location_id' => (int)$blast->location_id,
            'send_after_days' => (int)$blast->send_after_days,
            'categories' => $blast->categories,
            'brands' => $blast->brands,
            'include_archived' => (int)$blast->include_archived,
            'is_delivered' => (int)$blast->is_delivered,
            'is_cancelled' => (int)$blast->is_cancelled,
            'is_error' => (int)$blast->is_error,
            'send_date' => $blast->send_date,
            'created_at' => $blast->created_at,
            'updated_at' => $blast->updated_at,
            'deleted' => (int)$blast->deleted,
            'log' => $blast->log,
        ];
    }

    public function includeLeads(Blast $blast)
    {
        return $this->collection($blast->leads, $this->leadTransformer);
    }

    public function includeReport(Blast $blast)
    {
        return $this->item($blast->stats, $this->reportTransformer);
    }
}
