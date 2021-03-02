<?php

namespace App\Services\CRM\Email;

use App\Services\CRM\Email\DTOs\ImapConfig;
use App\Services\Integration\Common\DTOs\ParsedEmail;

interface ImapServiceInterface {
    /**
     * Import Email Replies
     * 
     * @param ImapConfig $imapConfig
     * @throws App\Exceptions\CRM\Email\ImapConnectionFailedException
     * @throws App\Exceptions\CRM\Email\ImapFolderConnectionFailedException
     * @throws App\Exceptions\CRM\Email\ImapFolderUnknownErrorException
     * @return array of emails
     */
    public function messages(ImapConfig $imapConfig);

    /**
     * Get Basic Overview
     * 
     * @param string $mailId
     * @return array of parsed data
     */
    public function overview(string $mailId);

    /**
     * Full Reply Details to Clean Up Result
     * 
     * @param ParsedEmail $email
     * @return ParsedEmail updated with additional details
     */
    public function full(ParsedEmail $email);
}