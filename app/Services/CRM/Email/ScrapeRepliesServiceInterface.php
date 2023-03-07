<?php

namespace App\Services\CRM\Email;

use App\Models\User\NewDealerUser;
use App\Models\CRM\User\SalesPerson;
use App\Models\CRM\User\EmailFolder;
use App\Models\Integration\Auth\AccessToken;

interface ScrapeRepliesServiceInterface {
    /**
     * Process Dealer
     * 
     * @param User $dealer
     * @return bool
     */
    public function dealer(NewDealerUser $dealer): bool;

    /**
     * Process Sales Person
     * 
     * @param NewDealerUser $dealer
     * @param SalesPerson $salesperson
     * @return int total number of imported emails
     */
    public function salesperson(NewDealerUser $dealer, SalesPerson $salesperson): int;

    /**
     * Import Single Folder
     * 
     * @param NewDealerUser $dealer
     * @param SalesPerson $salesperson
     * @param Folder $folder
     * @param AccessToken null|$accessToken
     * @return int total number of imported emails
     */
    public function folder(NewDealerUser $dealer, SalesPerson $salesperson,
                            EmailFolder $folder, ?AccessToken $accessToken = null): int;
}