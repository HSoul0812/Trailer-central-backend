<?php

namespace App\Transformers\Marketing;

use App\Models\Marketing\VirtualCard;
use App\Transformers\User\UserTransformer;
use League\Fractal\TransformerAbstract;

/**
 * Class VirtualCardTransformer
 * 
 * @package App\Transformers\Marketing
 */
class VirtualCardTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'dealer'
    ];

    /**
     * @var UserTransformer
     */
    protected $userTransformer;

    public function __construct(
        UserTransformer $userTransformer
    ) {
        $this->userTransformer = $userTransformer;
    }

    /**
     * @param VirtualCard $card
     * @return array
     */
    public function transform(VirtualCard $card): array
    {
        return [
            'id' => $card->id,
            'dealer_id' => $card->dealer_id,
            'type' => $card->type,
            'card_number' => $card->card_number,
            'security' => $card->security,
            'name_on_card' => $card->name_on_card,
            'address_street' => $card->address_street,
            'address_city' => $card->address_city,
            'address_state' => $card->address_state,
            'address_zip' => $card->address_zip,
            'expires_at' => $card->expires_at
        ];
    }

    public function includeDealer(VirtualCard $card)
    {
        return $this->collection($card->dealer, $this->userTransformer);
    }
}