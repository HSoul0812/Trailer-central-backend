<?php

namespace App\Services\CRM\Email;

use App\Services\CRM\Email\DTOs\ConfigValidate;
use App\Services\CRM\Email\DTOs\ImapConfig;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use Webklex\PHPIMAP\Message;
use Webklex\PHPIMAP\Support\MessageCollection;
use Illuminate\Support\Collection;

interface ImapServiceInterface {
    /**
     * Validate Imap
     *
     * @param ImapConfig $imapConfig
     * @return ConfigValidate
     */
    public function validate(ImapConfig $imapConfig): ConfigValidate;

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
     * Import Mailboxes
     *
     * @param ImapConfig $imapConfig
     * @throws App\Exceptions\CRM\Email\ImapConnectionFailedException
     * @throws App\Exceptions\CRM\Email\ImapFolderConnectionFailedException
     * @throws App\Exceptions\CRM\Email\ImapFolderUnknownErrorException
     * @return Collection<ImapMailbox>
     */
    public function mailboxes(ImapConfig $imapConfig): Collection;

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
    public function full(Message $message, ParsedEmail $email): ParsedEmail;
}