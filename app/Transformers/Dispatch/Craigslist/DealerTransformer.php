<?php

namespace App\Transformers\Dispatch\Craigslist;

use App\Transformers\Dispatch\TunnelTransformer;
use App\Transformers\Marketing\VirtualCardTransformer;
use App\Transformers\Marketing\Craigslist\AccountTransformer;
use App\Transformers\Marketing\Craigslist\ProfileTransformer;
use App\Services\Dispatch\Craigslist\DTOs\DealerCraigslist;
use League\Fractal\TransformerAbstract;

/**
 * Class DealerTransformer
 * 
 * @package App\Transformers\Dispatch\Craigslist
 */
class DealerTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'accounts',
        'profiles',
        'cards',
        'tunnels',
    ];


    /**
     * @param AccountTransformer $accountTransformer
     * @param ProfileTransformer $profileTransformer
     * @param TunnelTransformer $tunnelTransformer
     * @param VirtualCardTransformer $cardTransformer
     */
    public function __construct(
        AccountTransformer $accountTransformer,
        ProfileTransformer $profileTransformer,
        TunnelTransformer $tunnelTransformer,
        VirtualCardTransformer $cardTransformer
    ) {
        $this->accountTransformer = $accountTransformer;
        $this->profileTransformer = $profileTransformer;
        $this->tunnelTransformer = $tunnelTransformer;
        $this->cardTransformer = $cardTransformer;
    }

    /**
     * @param DealerCraigslist $clapp
     * @return array
     */
    public function transform(DealerCraigslist $clapp): array
    {
        return [
            'dealer' => [
                'id'    => $clapp->dealerId,
                'name'  => $clapp->dealerName,
                'email' => $clapp->dealerEmail,
                'type'  => $clapp->dealerType,
                'state' => $clapp->dealerState
            ],
            'slots'                => $clapp->slots,
            'chrome_mode'          => $clapp->chromeMode,
            'marketing_enabled_at' => $clapp->since,
            'next_scheduled'       => $clapp->next
        ];
    }

    public function includeAccounts(DealerCraigslist $dealer)
    {
        return $this->collection($dealer->accounts, $this->accountTransformer);
    }

    public function includeProfiles(DealerCraigslist $dealer)
    {
        return $this->collection($dealer->profiles, $this->profileTransformer);
    }

    public function includeTunnels(DealerCraigslist $dealer)
    {
        return $this->collection($dealer->tunnels, $this->tunnelTransformer);
    }

    public function includeCards(DealerCraigslist $dealer)
    {
        return $this->collection($dealer->cards, $this->cardTransformer);
    }
}