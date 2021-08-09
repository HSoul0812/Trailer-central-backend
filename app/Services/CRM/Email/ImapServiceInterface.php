<?php

namespace App\Services\CRM\Email;

use App\Services\CRM\Email\DTOs\ImapConfig;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use Webklex\IMAP\Message;
use Webklex\IMAP\Support\MessageCollection;

interface ImapServiceInterface {
    /**
     * Import Email Replies
     *
     * @param ImapConfig $imapConfig
     * @throws App\Exceptions\CRM\Email\ImapConnectionFailedException
     * @throws App\Exceptions\CRM\Email\ImapFolderConnectionFailedException
     * @throws App\Exceptions\CRM\Email\ImapFolderUnknownErrorException
     * @return MessageCollection
     */
    public function messages(ImapConfig $imapConfig): MessageCollection;

    /**
     * Get Basic Overview
     *
     * @param Message $overview
     * @return ParsedEmail
     */
    public function overview(Message $overview): ParsedEmail;

    /**
     * Full Reply Details to Clean Up Result
     *
     * @param Message $message
     * @param ParsedEmail $email
     * @return ParsedEmail updated with additional details
     */
    public function full(Message $message, ParsedEmail $email);
}