<?php

namespace App\Services\CRM\Email;

interface ScrapeRepliesServiceInterface {
    /**
     * Import Single Folder
     * 
     * @param NewDealerUser $dealer
     * @param SalesPerson $salesperson
     * @param Folder $folder
     * @return total number of imported emails
     */
    public function import($dealer, $salesperson, $folder);
}