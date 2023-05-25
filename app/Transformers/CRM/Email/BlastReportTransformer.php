<?php

namespace App\Transformers\CRM\Email;

use App\Services\CRM\Email\DTOs\BlastStats;
use League\Fractal\TransformerAbstract;

class BlastReportTransformer extends TransformerAbstract
{
    public function transform(BlastStats $stats)
    {
        return [
            'sent' => (int) $stats->sent,
            'delivered' => (int) $stats->delivered,
            'bounced' => (int) $stats->bounced,
            'complaints' => (int) $stats->complaints,
            'unsubscribed' => (int) $stats->unsubscribed,
            'opened' => (int) $stats->opened,
            'clicked' => (int) $stats->clicked,
            'skipped' => (int) $stats->skipped,
            'failed' => (int) $stats->failed
        ];
    }
}
