<?php

namespace App\Services\CRM\Text;

use App\Models\User\NewDealerUser;
use App\Models\CRM\Text\Campaign;
use Illuminate\Support\Collection;

interface CampaignServiceInterface {
    /**
     * Send Campaign Text
     * 
     * @param NewDealerUser $dealer
     * @param Campaign $campaign
     * @throws NoCampaignSmsFromNumberException
     * @throws NoLeadsProcessCampaignException
     * @return Collection<CampaignSent>
     */
    public function send(NewDealerUser $dealer, Campaign $campaign): Collection;
}