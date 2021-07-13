<?php

namespace App\Transformers\CRM\Email;

use App\Services\CRM\Interactions\DTOs\BuilderStats;
use League\Fractal\TransformerAbstract;

class BuilderStatsTransformer extends TransformerAbstract {

    /**
     * Transform BuilderStats
     * 
     * @param BuilderStats
     * @return array
     */
    public function transform(BuilderStats $stats)
    {
        return [
            'sent' => $stats->noSent,
            'bounced' => $stats->noBounced,
            'skipped' => $stats->skipped,
            'duplicates' => $stats->noDups,
            'errors' => $stats->noErrors
        ];
    }
}
