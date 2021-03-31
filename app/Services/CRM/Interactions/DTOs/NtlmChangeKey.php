<?php

namespace App\Services\CRM\Interactions\DTOs;

/**
 * Class NtlmChangeKey
 * 
 * @package App\Services\CRM\Interactions\DTOs
 */
class NtlmChangeKey
{
    /**
     * @var string Item ID From NTLM
     */
    private $itemId;

    /**
     * @var string Change Key From NTLM
     */
    private $changeKey;

    /**
     * @var string Attachment ID From NTLM
     */
    private $attachId;


    /**
     * Return Item Id
     * 
     * @return string $this->itemId
     */
    public function getItemId(): string
    {
        return $this->itemId;
    }

    /**
     * Set Item Id
     * 
     * @param string $itemId
     * @return void
     */
    public function setItemId(string $itemId): void
    {
        $this->itemId = $itemId;
    }


    /**
     * Return Change Key
     * 
     * @return string $this->changeKey
     */
    public function getChangeKey(): string
    {
        return $this->changeKey;
    }

    /**
     * Set ChangeKey
     * 
     * @param string $changeKey
     * @return void
     */
    public function setChangeKey(string $changeKey): void
    {
        $this->changeKey = $changeKey;
    }


    /**
     * Return Attachment Id
     * 
     * @return string $this->attachId
     */
    public function getAttachId(): string
    {
        return $this->attachId;
    }

    /**
     * Set Attach Id
     * 
     * @param string $attachId
     * @return void
     */
    public function setAttachId(string $attachId): void
    {
        $this->attachId = $attachId;
    }
}