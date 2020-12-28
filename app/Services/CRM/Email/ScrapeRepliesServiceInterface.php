<?php

namespace App\Services\CRM\Email;

interface ScrapeRepliesServiceInterface {
    /**
     * Process Dealer
     * 
     * @param User $dealer
     */
    public function dealer(NewDealerUser $dealer);

    /**
     * Process Sales Person
     * 
     * @param NewDealerUser $dealer
     * @param SalesPerson $salesperson
     * @return false || array of EmailHistory
     */
    public function salesperson(NewDealerUser $dealer, SalesPerson $salesperson);

    /**
     * Import Single Folder
     * 
     * @param NewDealerUser $dealer
     * @param SalesPerson $salesperson
     * @param Folder $folder
     * @return total number of imported emails
     */
    public function folder(NewDealerUser $dealer, SalesPerson $salesperson, EmailFolder $folder);
}