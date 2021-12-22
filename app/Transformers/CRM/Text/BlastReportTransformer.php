<?php

namespace App\Transformers\CRM\Text;

use App\Models\CRM\Text\BlastStats;
use League\Fractal\TransformerAbstract;

class BlastReportTransformer extends TransformerAbstract
{
    public function transform(BlastStats $stats)
    {
        return [
            'skipped'      => (int) $stats->skipped,
            'sent'         => (int) $stats->sent,
            'failed'       => (int) $stats->failed,
            'unsubscribed' => (int) $stats->unsubscribed
        ];
    }
}
