<?php

namespace App\Services\CRM\Email;

interface ImapServiceInterface {
    /**
     * Import Email Replies
     * 
     * @param SalesPerson $salesperson
     * @param EmailFolder $folder
     * @throws App\Exceptions\CRM\Email\ImapConnectionFailedException
     * @throws App\Exceptions\CRM\Email\ImapFolderConnectionFailedException
     * @throws App\Exceptions\CRM\Email\ImapFolderUnknownErrorException
     * @return array of emails
     */
    public function messages(SalesPerson $salesperson, EmailFolder $folder);

    /**
     * Get Basic Overview
     * 
     * @param int $mailId
     * @return array of parsed data
     */
    public function overview(int $mailId);

    /**
     * Parse Reply Details to Clean Up Result
     * 
     * @param array $overview
     * @return array of parsed data
     */
    public function parsed(array $overview);
}