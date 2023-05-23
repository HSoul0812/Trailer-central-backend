<?php

namespace App\Services\CRM\Interactions\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;
use Illuminate\Support\Collection;

/**
 * Class EmailDraft
 * 
 * @package App\Services\CRM\Interactions\DTOs
 */
class EmailDraft 
{
    use WithConstructor, WithGetter;

    private $emailId;

    private $leadId;

    private $interactionId;

    private $messageId;

    private $subject;

    private $body;

    private $fromEmail;

    private $fromName;

    private $toEmail;

    private $toName;

    private $replytoEmail;

    private $replytoName;

    /**
     * @var Collection<EmailDraftAttachment>
     */
    private $attachments;

    public function getAttachments()
    {
        return $this->attachments->toArray();
    }
}