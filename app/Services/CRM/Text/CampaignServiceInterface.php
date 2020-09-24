<?php

namespace App\Services\CRM\Text;

interface CampaignServiceInterface {
    /**
     * Send Campaign Text
     * 
     * @param NewDealerUser $dealer
     * @param Campaign $campaign
     * @return false || array of CampaignSent
     */
    public function send($dealer, $campaign);
}