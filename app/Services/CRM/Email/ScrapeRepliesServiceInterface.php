<?php

namespace App\Services\CRM\Email;

interface ScrapeRepliesServiceInterface {
    /**
     * Import Email Replies
     * 
     * @param SalesPerson $salesperson
     * @return false || array of EmailHistory
     */
    public function import($salesperson);
}