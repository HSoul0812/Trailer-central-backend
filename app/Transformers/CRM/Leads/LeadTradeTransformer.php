<?php

namespace App\Transformers\CRM\Leads;

use App\Models\CRM\Leads\LeadTrade;
use League\Fractal\TransformerAbstract;

/**
 * Class LeadTradeTransformer
 * @package App\Transformers\CRM\Leads
 */
class LeadTradeTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'images'
    ];

    /**
     * @var LeadTradeImageTransformer
     */
    protected $leadTradeImageTransformer;

    public function __construct()
    {
        $this->leadTradeImageTransformer = new LeadTradeImageTransformer();
    }

    /**
     * @param LeadTrade $leadTrade
     * @return array
     */
    public function transform(LeadTrade $leadTrade): array
    {
        return  [
            'id' => $leadTrade->id,
            'lead_id' => $leadTrade->lead_id,
            'type' => $leadTrade->type,
            'make' => $leadTrade->make,
            'model' => $leadTrade->model,
            'year' => $leadTrade->year,
            'price' => $leadTrade->price,
            'length' => $leadTrade->length,
            'width' => $leadTrade->width,
            'notes' => $leadTrade->notes,
            'created_at' => $leadTrade->created_at ? (new \DateTime($leadTrade->created_at))->format('Y-m-d H:i:s') : $leadTrade->created_at
        ];
    }

    public function includeImages(LeadTrade $leadTrade)
    {
        if (empty($leadTrade->images)) {
            return [];
        }

        return $this->collection($leadTrade->images, $this->leadTradeImageTransformer);
    }
}
