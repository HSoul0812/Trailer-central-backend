<?php

namespace App\Services\CRM\Text;

interface BlastServiceInterface {
    /**
     * Send Blast Text
     * 
     * @param NewDealerUser $dealer
     * @param Blast $blast
     * @return false || array of BlastSent
     */
    public function send($dealer, $blast);
}