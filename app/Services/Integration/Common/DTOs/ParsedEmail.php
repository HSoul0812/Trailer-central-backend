<?php

namespace App\Services\Integration\Common\DTOs;

use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Class ParsedEmail
 * 
 * @package App\Services\Integration\Common\DTOs
 */
class ParsedEmail
{
    /**
     * @var string ID of Email from Source
     */
    private $id;

    /**
     * @var string Message ID
     */
    private $messageId;

    /**
     * @var string First Message ID in Chain
     */
    private $rootMessageId;

    /**
     * @var string References to Past Replies, Separated by Spaces
     */
    private $references;


    /**
     * @var string Email Address Sent To
     */
    private $to;

    /**
     * @var string Name of Person Email Sent To
     */
    private $toName;

    /**
     * @var string Email Address Sent From
     */
    private $from;

    /**
     * @var string Name of Person Email Sent From
     */
    private $fromName;


    /**
     * @var string Subject of Email
     */
    private $subject;

    /**
     * @var string Body of Email
     */
    private $body;

    /**
     * @var bool Is Email HTML?
     */
    private $isHtml;

    /**
     * @var Collection<AttachmentFile> Attachments Sent With Email
     */
    private $attachments;

    /**
     * @var string Date of email sent
     */
    private $date;


    /**
     * @var int Lead ID Associated With Email
     */
    private $leadId;

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
    public function setId(string$id): void
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
        return $this->messageId;
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
        if (empty($this->references)) {
            return [];
        }
        return explode(" ", $parsed['references']);
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
    public function getTo($to): string
    {
        // Name Exists?
        if(!empty($this->toName)) {
            return $this->toName . ' <' . $this->to . '>';
        }

        // Return To Email
        return $this->to;
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
            $to = str_replace('>', '', end($parts));
            $name = trim(reset($parts));
        }

        // Set To
        $this->setToEmail($to);
        $this->setToName($name);
    }

    /**
     * Return To Email
     * 
     * @return string $this->to
     */
    public function getToEmail(): string
    {
        return $this->to;
    }

    /**
     * Set To Email
     * 
     * @param string $to
     * @return void
     */
    public function setToEmail(string $to): void
    {
        $this->to = $to;
    }

    /**
     * Return To Email
     * 
     * @return string $this->toName
     */
    public function getToName(): string
    {
        return $this->toName;
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
     * Return Full From
     * 
     * @return string $this->from || $this->fromName ($this->from)
     */
    public function getFrom($from): string
    {
        // Name Exists?
        if(!empty($this->fromName)) {
            return $this->fromName . ' <' . $this->from . '>';
        }

        // Return From Email
        return $this->from;
    }

    /**
     * Set From, Parsing Both Email and Name
     * 
     * @param string $fromFull
     * @return void
     */
    public function setFrom(string $fromFull): void
    {
        // Separate Name From Email
        $email = $fromFull;
        $name = '';
        if(strpos($fromFull, '<') !== FALSE) {
            $parts = explode("<", $fromFull);
            $from = str_replace('>', '', end($parts));
            $name = trim(reset($parts));
        }

        // Set From
        $this->setFromEmail($from);
        $this->setFromName($name);
    }

    /**
     * Return From Email
     * 
     * @return string $this->from
     */
    public function getFromEmail(): string
    {
        return $this->from;
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
        return $this->fromName;
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
        return $this->subject;
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
        return $this->body;
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
     * Return Is HTML
     * 
     * @return bool $this->isHtml
     */
    public function getIsHtml(): bool
    {
        return $this->isHtml;
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
        return $this->attachments;
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
        $this->attachments->push($attachment);
    }


    /**
     * Return Date
     * 
     * @return string $this->date
     */
    public function getDate(): string
    {
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
        $this->date = Carbon::parse($date)->toDateTimeString();
    }


    /**
     * Return Lead ID
     * 
     * @return int $this->lead_id
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
}