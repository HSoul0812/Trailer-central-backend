<?php

namespace App\Transformers\CRM\Leads;

use App\Models\CRM\Leads\LeadTradeImage;
use League\Fractal\TransformerAbstract;

/**
 * Class LeadTradeImageTransformer
 * @package App\Transformers\CRM\Leads
 */
class LeadTradeImageTransformer extends TransformerAbstract
{
    /**
     * @param LeadTradeImage $leadTradeImage
     * @return array
     */
    public function transform(LeadTradeImage $leadTradeImage): array
    {
        return  [
            'id' => $leadTradeImage->id,
            'trade_id' => $leadTradeImage->trade_id,
            'filename' => $leadTradeImage->filename,
            'path' => $leadTradeImage->path,
            'created_at' => $leadTradeImage->created_at ? (new \DateTime($leadTradeImage->created_at))->format('Y-m-d H:i:s') : $leadTradeImage->created_at
        ];
    }
}
