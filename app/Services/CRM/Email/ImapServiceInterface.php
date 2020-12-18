<?php

namespace App\Services\CRM\Email;

interface ImapServiceInterface {
    /**
     * Import Email Replies
     * 
     * @param SalesPerson $salesperson
     * @param EmailFolder $folder
     * @return false || array of EmailHistory
     */
    public function messages($salesperson, $folder);
}