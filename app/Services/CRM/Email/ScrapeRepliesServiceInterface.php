<?php

namespace App\Services\CRM\Email;

interface ScrapeRepliesServiceInterface {
    /**
     * Import Email Replies
     * 
     * @param NewDealerUser $dealer
     * @param SalesPerson $salesperson
     * @return false || array of EmailHistory
     */
    public function import($dealer, $salesperson);
}