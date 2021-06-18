<?php

namespace App\DTO\CRM\Leads\Export;

class LeadEmail 
{
    /**
     * @var int
     */
    private $dealerId;
    
    /**
     * @var string
     */
    private $email;
    
    /**
     * @var int
     */
    private $exportFormat;
    
    /**
     * @var string
     */
    private $ccEmail;
    
    /**
     * @var int
     */
    private $dealerLocationId;
    
    public function __construct(int $dealerId, string $email, int $exportFormat, string $ccEmail, int $dealerLocationId) {
        $this->dealerId = $dealerId;
        $this->email = $email;
        $this->exportFormat = $exportFormat;
        $this->ccEmail = $ccEmail;
        $this->dealerLocationId = $dealerLocationId;
    }
    
    public function getDealerId(): int {
        return $this->dealerId;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getExportFormat(): int {
        return $this->exportFormat;
    }

    public function getCcEmail(): string {
        return $this->ccEmail;
    }

    public function getDealerLocationId(): int {
        return $this->dealerLocationId;
    }

}
