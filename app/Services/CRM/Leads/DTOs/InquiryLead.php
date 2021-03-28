<?php

namespace App\Services\CRM\Leads\DTOs;

use App\Models\CRM\Leads\LeadType;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;
use Carbon\Carbon;

/**
 * Class InquiryLead
 * 
 * @package App\Services\CRM\Leads\DTOs
 */
class InquiryLead
{
    use WithConstructor, WithGetter;

    /**
     * @var string Dealer ID for Inquiry Lead
     */
    private $dealerId;

    /**
     * @var string Dealer Location ID for Inquiry Lead
     */
    private $locationId;

    /**
     * @var string Website ID for Inquiry Lead
     */
    private $websiteId;

    /**
     * @var string Date Lead Was Requested
     */
    private $requestDate;


    /**
     * @var string First Name for Inquiry Lead
     */
    private $firstName;

    /**
     * @var string Last Name for Inquiry Lead
     */
    private $lastName;

    /**
     * @var string Email Address for Inquiry Lead
     */
    private $email;

    /**
     * @var string Phone Number for Inquiry Lead
     */
    private $phone;

    /**
     * @var string Comments for Inquiry Lead
     */
    private $comments;


    /**
     * Return Lead Type
     * 
     * @return string $this->leadType || calculate lead type
     */
    public function getLeadType(): string
    {
        // Calculate Lead Type?
        if(empty($this->leadType)) {
            if(!empty($this->inventoryId)) {
                $this->leadType = LeadType::TYPE_INVENTORY;
            } else {
                $this->leadType = LeadType::TYPE_GENERAL;
            }
        }

        // Return Lead Type
        return $this->leadType;
    }


    /**
     * Return Full Name
     * 
     * @return string $this->firstName $this->lastName
     */
    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }


    /**
     * Return Preferred Contact
     * 
     * @return $this->preferredContact || string 'phone' if phone exists, 'email' otherwise
     */
    public function getPreferredContact(): string
    {
        // Get Preferred Contact
        if(!empty($this->preferredContact)) {
            return $this->preferredContact;
        }

        // Return if Phone Exists
        if(!empty($this->phone)) {
            return self::PREFERRED_PHONE;
        }

        // Return if Email Exists
        return (!empty($this->email) ? self::PREFERRED_EMAIL : self::PREFERRED_PHONE);
    }


    /**
     * Build Subject
     * 
     * @param array $data
     */
    public function getSubject($data) {
        // Initialize
        switch($this->inquiryType) {
            case 'call':
                $subject = "You Just Received a Click to Call From %s";
                return sprintf($subject, $this->getFullName());
            case 'inventory':
                $subject = 'Inventory Information Request on %s';
            break;
            case 'part':
                $subject = "Inventory Part Information Request on %s";
            break;
            case 'showroom':
                $subject = "Showroom Model Information Request on %s";
            break;
            case 'cta':
                $subject = "New CTA Response on %s";
            break;
            case 'sms':
                $subject = "New SMS Sent on %s";
            break;
            case 'bestprice':
                $subject = 'New Get Best Price Information Request on %s';
            break;
            default:
                $subject = 'New General Submission on %s';
            break;
        }

        // Generate subject depending on type
        return sprintf($subject, $this->websiteDomain);
    }
}