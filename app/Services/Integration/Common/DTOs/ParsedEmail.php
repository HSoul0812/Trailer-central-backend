<?php

namespace App\Services\Integration\Common\DTOs;

use App\Models\CRM\Email\Attachment;
use App\Exceptions\CRM\Email\ExceededSingleAttachmentSizeException;
use App\Exceptions\CRM\Email\ExceededTotalAttachmentSizeException;
use App\Traits\MailHelper;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Class ParsedEmail
 * 
 * @package App\Services\Integration\Common\DTOs
 */
class ParsedEmail
{
    use MailHelper, WithConstructor, WithGetter;


    /**
     * Body HTML
     */
    const BODY_HTML = 'html';

    /**
     * Body Plain
     */
    const BODY_PLAIN = 'text';


    /**
     * @var string ID of Email from Source
     */
    private $id = '';

    /**
     * @var string Message ID
     */
    private $messageId = '';

    /**
     * @var string First Message ID in Chain
     */
    private $rootMessageId = '';

    /**
     * @var string References to Past Replies, Separated by Spaces
     */
    private $references = '';


    /**
     * @var string Email Address Sent To
     */
    private $to = '';

    /**
     * @var string Name of Person Email Sent To
     */
    private $toName = '';

    /**
     * @var string Email Address Sent From
     */
    private $from = '';

    /**
     * @var string Name of Person Email Sent From
     */
    private $fromName = '';


    /**
     * @var string Subject of Email
     */
    private $subject = '';

    /**
     * @var string Body of Email
     */
    private $body = '';

    /**
     * @var bool Is Email HTML?
     */
    private $isHtml = false;

    /**
     * @var bool $hasAttachments
     */
    private $hasAttachments = false;

    /**
     * @var Collection<AttachmentFile> Attachments Sent With Email
     */
    private $attachments;

    /**
     * @var Collection<AttachmentFile> Attachments Already Saved to DB on Draft
     */
    private $existingAttachments;

    /**
     * @var string Date of email sent
     */
    private $date = '';


    /**
     * @var int Email History ID Associated With Email
     */
    private $emailHistoryId = 0;

    /**
     * @var int Lead ID Associated With Email
     */
    private $leadId = 0;

    /**
     * @var int Quote ID Associated With Email
     */
    private $quoteId = 0;

    /**
     * @var int Interaction ID Associated With Email
     */
    private $interactionId = 0;

    /**
     * @var string Received || Sent (By || From Sales Person)
     */
    private $direction = 'Received';


    /**
     * Return ID
     * 
     * @return string $this->id
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set ID
     * 
     * @param string $id
     * @return void
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }


    /**
     * Return Message ID
     * 
     * @return string $this->messageId
     */
    public function getMessageId(): string
    {
        // No Message ID Exists?
        if(empty($this->messageId)) {
            // Get Unique Message ID
            $this->messageId = sprintf('%s@%s', $this->generateId(), $this->serverHostname());
        }

        // Return Message ID
        return $this->messageId;
    }

    /**
     * Return Clean Message ID
     * 
     * @return string $this->messageId
     */
    public function cleanMessageId(): string
    {
        if($this->messageId) {
            return preg_replace("/[<>]/", "", $this->messageId);
        }
        return '';
    }

    /**
     * Set Message ID
     * 
     * @param string $messageId
     * @return void
     */
    public function setMessageId(string $messageId): void
    {
        $this->messageId = trim($messageId);
    }


    /**
     * Return Root Message ID
     * 
     * @return string $this->rootMessageId || $this->messageId
     */
    public function getRootMessageId(): string
    {
        return !empty($this->rootMessageId) ? trim($this->rootMessageId) : $this->messageId;
    }

    /**
     * Set Root Message ID
     * 
     * @param string $rootMessageId
     * @return void
     */
    public function setRootMessageId(string $rootMessageId): void
    {
        $this->rootMessageId = $rootMessageId;
    }


    /**
     * Return References Converted From String to Array
     * 
     * @return array $this->references
     */
    public function getReferences(): array
    {
        return !empty($this->references) ? explode(" ", $this->references) : [];
    }

    /**
     * Return First Reference
     * 
     * @return string reset($this->getReferences())
     */
    public function getFirstReference(): string
    {
        $references = $this->getReferences();
        return !empty($references) ? reset($references) : '';
    }

    /**
     * Return Last Reference
     * 
     * @return string end($this->getReferences())
     */
    public function getLastReference(): string
    {
        $references = $this->getReferences();
        return !empty($references) ? end($references) : '';
    }

    /**
     * Set References
     * 
     * @param string $references
     * @return void
     */
    public function setReferences(string $references): void
    {
        $this->references = $references;
    }


    /**
     * Return Full To
     * 
     * @return string $this->to || $this->toName ($this->to)
     */
    public function getTo(): string
    {
        return !empty($this->toName) ? $this->toName . ' <' . $this->to . '>' : $this->to;
    }

    /**
     * Set To, Parsing Both Email and Name
     * 
     * @param string $toFull
     * @return void
     */
    public function setTo(string $toFull): void
    {
        // Separate Name From Email
        $email = $toFull;
        $name = '';
        if(strpos($toFull, '<') !== FALSE) {
            $parts = explode("<", $toFull);
            $email = str_replace('>', '', end($parts));
            $name = trim(reset($parts));
        }

        // Set To
        $this->setToEmail($email);
        $this->setToName($name);
    }

    /**
     * Return To Email
     * 
     * @return string $this->to
     */
    public function getToEmail(): string
    {
        return $this->to ?? '';
    }

    /**
     * Set To Email
     * 
     * @param string $to
     * @return void
     */
    public function setToEmail(?string $to = null): void
    {
        $this->to = $to ?? '';
    }

    /**
     * Return To Email
     * 
     * @return string $this->toName
     */
    public function getToName(): string
    {
        return $this->toName ?? '';
    }

    /**
     * Set To Name
     * 
     * @param string $toName
     * @return void
     */
    public function setToName(string $toName): void
    {
        $this->toName = $toName;
    }

    /**
     * Get To Array
     * 
     * @return array{email: string, ?name: string}
     */
    public function getToArray() {
        // Initialize To Array
        $to = ['email' => trim($this->to)];

        // Append Name
        if($this->toName) {
            $to['name'] = trim($this->toName);
        }

        // Return To
        return $to;
    }


    /**
     * Return Full From
     * 
     * @return string $this->from || $this->fromName ($this->from)
     */
    public function getFrom(): string
    {
        return !empty($this->fromName) ? $this->fromName . ' <' . $this->from . '>' : $this->from;
    }

    /**
     * Set From, Parsing Both Email and Name
     * 
     * @param string $fromFull
     * @return void
     */
    public function setFrom(?string $fromFull = null): void
    {
        // Separate Name From Email
        $email = $fromFull ?? '';
        $name = '';
        if(strpos($fromFull, '<') !== FALSE) {
            $parts = explode("<", $fromFull);
            $email = str_replace('>', '', end($parts));
            $name = trim(reset($parts));
        }

        // Set From
        $this->setFromEmail($email);
        $this->setFromName($name);
    }

    /**
     * Return From Email
     * 
     * @return string $this->from
     */
    public function getFromEmail(): string
    {
        return $this->from ?? '';
    }

    /**
     * Set From Email
     * 
     * @param string $from
     * @return void
     */
    public function setFromEmail(string $from): void
    {
        $this->from = $from;
    }

    /**
     * Return From Email
     * 
     * @return string $this->fromName
     */
    public function getFromName(): string
    {
        return $this->fromName ?? '';
    }

    /**
     * Set From Name
     * 
     * @param string $fromName
     * @return void
     */
    public function setFromName(string $fromName): void
    {
        $this->fromName = $fromName;
    }


    /**
     * Return Subject
     * 
     * @return string $this->subject
     */
    public function getSubject(): string
    {
        return $this->subject ?? '';
    }

    /**
     * Set Subject
     * 
     * @param string $subject
     * @return void
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }


    /**
     * Return Body
     * 
     * @return string $this->body
     */
    public function getBody(): string
    {
        return $this->body ?? '';
    }

    /**
     * Set Body
     * 
     * @param string $body
     * @param bool $changeIsHtml
     * @return void
     */
    public function setBody(string $body, bool $changeIsHtml = true): void
    {
        $this->body = $body;

        // Set Is HTML?
        if($changeIsHtml) {
            $this->setIsHtml(strip_tags($body) !== $body);
        }
    }

    /**
     * Return Body Type
     * 
     * @return string self::BODY_HTML | self::BODY_PLAIN
     */
    public function getBodyType(): string
    {
        return $this->getIsHtml() ? self::BODY_HTML : self::BODY_PLAIN;
    }


    /**
     * Return Is HTML
     * 
     * @return bool $this->isHtml
     */
    public function getIsHtml(): bool
    {
        return (bool) $this->isHtml;
    }

    /**
     * Set Is HTML
     * 
     * @param bool $isHtml
     * @return void
     */
    public function setIsHtml(bool $isHtml): void
    {
        $this->isHtml = $isHtml;
    }


    /**
     * Return Attachments
     * 
     * @return Collection<AttachmentFile> $this->attachments
     */
    public function getAttachments(): Collection
    {
        // Attachments Exist?
        if(!empty($this->attachments)) {
            return $this->attachments;
        }

        // Return Empty Collection
        return new Collection();
    }

    /**
     * Set Attachments
     * 
     * @param Collection<AttachmentFile> $attachments
     * @return void
     */
    public function setAttachments(Collection $attachments): void
    {
        $this->attachments = $attachments;
        $this->hasAttachments = true;
    }

    /**
     * Add Attachment
     * 
     * @param AttachmentFile $attachment
     * @return void
     */
    public function addAttachment(AttachmentFile $attachment): void
    {
        if(empty($this->attachments)) {
            $this->attachments = new Collection();
        }

        // Append Attachment
        $this->hasAttachments = true;
        $this->attachments->push($attachment);
    }

    /**
     * Validate Attachments Size
     * 
     * @throws ExceededSingleAttachmentSizeException
     * @throws ExceededTotalAttachmentSizeException
     * @return int
     */
    public function validateAttachmentsSize() {
        // Loop Attachments
        $totalSize = 0;
        if (!empty($this->attachments)) {
            foreach($this->attachments as $attachment) {
                if ($attachment->getFileSize() > Attachment::MAX_FILE_SIZE) {
                    throw new ExceededSingleAttachmentSizeException();
                } else if ($totalSize > Attachment::MAX_UPLOAD_SIZE) {
                    throw new ExceededTotalAttachmentSizeException();
                }
                $totalSize += $attachment->getFileSize();
            }
        }        

        // Return Total Size
        return $totalSize;
    }

    /**
     * Do Attachments Exist?
     * 
     * @return bool
     */
    public function hasAttachments(): bool
    {
        return !empty($this->attachments) ? ($this->attachments->isNotEmpty()) : false;
    }


    /**
     * Return Existing Attachments
     * 
     * @return Collection<AttachmentFile> $this->existingAttachments
     */
    public function getExistingAttachments(): Collection
    {
        // Attachments Exist?
        if(!empty($this->existingAttachments)) {
            return $this->existingAttachments;
        }

        // Return Empty Collection
        return new Collection();
    }

    /**
     * Set Existing Attachments
     * 
     * @param Collection<AttachmentFile> $attachments
     * @return void
     */
    public function setExistingAttachments(Collection $attachments): void
    {
        $this->existingAttachments = $attachments;
    }

    /**
     * Add Existing Attachment
     * 
     * @param AttachmentFile $attachment
     * @return void
     */
    public function addExistingAttachment(AttachmentFile $attachment): void
    {
        if(empty($this->existingAttachments)) {
            $this->existingAttachments = new Collection();
        }

        // Append Existing Attachment
        $this->hasAttachments = true;
        $this->existingAttachments->push($attachment);
    }


    /**
     * Merge All Attachments
     * 
     * @return Collection<AttachmentFile> merge($this->attachments, $this->existingAttachments)
     */
    public function getAllAttachments(): Collection
    {
        // Initialize Collection
        $attachments = new Collection();

        // Attachments Exist?
        if(!empty($this->attachments)) {
            $attachments = $attachments->merge($this->attachments);
        }

        // Existing Attachments Exist?
        if(!empty($this->existingAttachments)) {
            $attachments = $attachments->merge($this->existingAttachments);
        }

        // Return Collection of All Attachments
        return $attachments;
    }


    /**
     * Return Date
     * 
     * @return string $this->date
     */
    public function getDate(): string
    {
        if(!$this->date) {
            $this->setDateNow();
        }
        return $this->date;
    }

    /**
     * Set Date
     * 
     * @param string $date
     * @return void
     */
    public function setDate(string $date): void
    {
        $validDate = preg_replace('/\s+\(.*?\)/', '', $date);
        $this->date = Carbon::parse($validDate)->setTimezone('UTC')->toDateTimeString();
    }

    /**
     * Set Date to Now
     * 
     * @return void
     */
    public function setDateNow(): void
    {
        $this->date = Carbon::now()->setTimezone('UTC')->toDateTimeString();
    }


    /**
     * Return Email History ID
     * 
     * @return int $this->emailHistoryId || null
     */
    public function getEmailHistoryId(): ?int
    {
        return $this->emailHistoryId;
    }

    /**
     * Set Email History ID
     * 
     * @param int $emailHistoryId ID Associated With Email History Entry
     * @return void
     */
    public function setEmailHistoryId(int $emailHistoryId): void
    {
        $this->emailHistoryId = $emailHistoryId;
    }


    /**
     * Return Lead ID
     * 
     * @return int $this->leadId
     */
    public function getLeadId(): int
    {
        return $this->leadId;
    }

    /**
     * Set Lead ID
     * 
     * @param int $leadId Lead ID Associated With Email
     * @return void
     */
    public function setLeadId(int $leadId): void
    {
        $this->leadId = $leadId;
    }

    /**
     * Return Quote ID
     * 
     * @return int $this->quoteId
     */
    public function getQuoteId(): int
    {
        return $this->quoteId;
    }

    /**
     * Set Quote ID
     * 
     * @param int $quoteId Quote ID Associated With Email
     * @return void
     */
    public function setQuoteId(int $quoteId): void
    {
        $this->quoteId = $quoteId;
    }

    /**
     * Return Interaction ID
     * 
     * @return int $this->interactionId || null
     */
    public function getInteractionId(): ?int
    {
        return $this->interactionId;
    }

    /**
     * Set Interaction ID
     * 
     * @param int $interactionId Interaction ID Associated With Email
     * @return void
     */
    public function setInteractionId(int $interactionId): void
    {
        $this->interactionId = $interactionId;
    }


    /**
     * Return Direction
     * 
     * @return string $this->direction
     */
    public function getDirection(): string
    {
        return $this->direction;
    }

    /**
     * Set Direction
     * 
     * @param string $direction Received || Sent From Sales Person
     * @return void
     */
    public function setDirection(string $direction): void
    {
        $this->direction = $direction;
    }


    /**
     * Return Email History Params
     * 
     * @return array{id: int,
     *               lead_id: int,
     *               interaction_id: int,
     *               message_id: string,
     *               root_message_id: string,
     *               to_email: string,
     *               to_name: string,
     *               from_email: string,
     *               from_name: string,
     *               subject: string,
     *               body: string,
     *               use_html: bool,
     *               date_sent: string,
     *               attachments: array
     */
    public function getParams(): array
    {
        // Get Attachment Params
        $attachments = [];
        
        if (!empty($this->attachments)) {
            foreach($this->attachments as $attachment) {
                $attachments[] = $attachment->getParams($this->messageId);
            }
        }        

        // Return Params
        return [
            'id' => $this->emailHistoryId,
            'lead_id' => $this->leadId,
            'interaction_id' => $this->interactionId,
            'message_id' => $this->messageId,
            'root_message_id' => $this->rootMessageId,
            'to_email' => $this->to,
            'to_name' => $this->toName,
            'from_email' => $this->from,
            'from_name' => $this->fromName,
            'subject' => $this->subject,
            'body' => $this->body,
            'use_html' => $this->isHtml,
            'date_sent' => $this->date,
            'attachments' => $attachments
        ];
    }
}