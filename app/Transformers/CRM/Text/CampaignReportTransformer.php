<?php

namespace App\Transformers\CRM\Text;

use App\Models\CRM\Text\CampaignStats;
use League\Fractal\TransformerAbstract;

class CampaignReportTransformer extends TransformerAbstract
{
    public function transform(CampaignStats $stats)
    {
        return [
            'skipped'      => (int) $stats->skipped,
            'sent'         => (int) $stats->sent,
            'failed'       => (int) $stats->failed,
            'unsubscribed' => (int) $stats->unsubscribed
        ];
    }
}
