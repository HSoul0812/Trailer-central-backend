<?php

namespace App\Services\CRM\Leads\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class InquiryLead
 * 
 * @package App\Services\CRM\Leads\DTOs
 */
class InquiryLead
{
    use WithConstructor, WithGetter;

    /**
     * @const string
     */
    const PREFERRED_PHONE = 'phone';

    /**
     * @const string
     */
    const PREFERRED_EMAIL = 'email';


    /**
     * @const string
     */
    const DEFAULT_EMAIL_BODY = '#ffffff';

    /**
     * @const string
     */
    const DEFAULT_EMAIL_HEADER = 'transparent';


    /**
     * @const string
     */
    const TT_DOMAIN = 'www.trailertrader.com';

    /**
     * @const string
     */
    const TT_EMAIL_BODY = '#ffff00';

    /**
     * @const string
     */
    const TT_EMAIL_HEADER = '#00003d';


    /**
     * @var string
     */
    const INQUIRY_TYPE_DEFAULT = 'general';

    /**
     * @var array
     */
    const INQUIRY_TYPES = array(
        'general',
        'cta',
        'inventory',
        'part',
        'showroom',
        'call',
        'sms'
    );



    /**
     * @var int Dealer ID for Lead Inquiry
     */
    private $dealerId;

    /**
     * @var int Dealer Location ID for Lead Inquiry
     */
    private $locationId;

    /**
     * @var int Website ID for Lead Inquiry
     */
    private $websiteId;

    /**
     * @var int Website Domain for Lead Inquiry
     */
    private $websiteDomain;


    /**
     * @var string Type of Lead Inquiry
     */
    private $inquiryType;

    /**
     * @var array<string> Lead Types to Insert to Lead Inquiry
     */
    private $leadTypes;

    /**
     * @var array<string> Units of Interest for the Lead Inquiry
     */
    private $inventory;


    /**
     * @var string Title of Lead Form / Name of Inventory Item Associated with Lead Inquiry
     */
    private $title;

    /**
     * @var string Referral of Lead Inquiry
     */
    private $referral;

    /**
     * @var ?string Stock Number of Unit of Interest on Lead Inquiry
     */
    private $stock;


    /**
     * @var string First Name for Lead Inquiry
     */
    private $firstName;

    /**
     * @var string Last Name for Lead Inquiry
     */
    private $lastName;

    /**
     * @var string Email Address for Lead Inquiry
     */
    private $emailAddress;

    /**
     * @var string Phone Number for Lead Inquiry
     */
    private $phoneNumber;


    /**
     * @var string Street Address for Lead Inquiry
     */
    private $address;

    /**
     * @var string City for Lead Inquiry
     */
    private $city;

    /**
     * @var string State for Lead Inquiry
     */
    private $state;

    /**
     * @var string Zip for Lead Inquiry
     */
    private $zip;


    /**
     * @var string Comments for Lead Inquiry
     */
    private $comments;

    /**
     * @var string Notes for Lead Inquiry
     */
    private $note;

    /**
     * @var string Metadata for Lead Inquiry
     */
    private $metadata;


    /**
     * @var ?string Date Lead Inquiry Was Submitted
     */
    private $dateSubmitted;

    /**
     * @var ?string Contact Email Sent for Lead Inquiry
     */
    private $contactEmailSent;

    /**
     * @var ?string ADF Email Sent for Lead Inquiry
     */
    private $adfEmailSent;

    /**
     * @var ?bool CDK Export Sent for Lead Inquiry?
     * 
     * 1 = mark CDK as sent automatically
     * 0 = wait for CDK script to send out the email on its own
     */
    private $cdkEmailSent;


    /**
     * @var bool Newsletter Requested on Lead Inquiry?
     */
    private $newsletter;

    /**
     * @var bool Is This Lead Inquiry Spam?
     */
    private $isSpam;


    /**
     * @var string Source of Lead Inquiry
     */
    private $leadSource;

    /**
     * @var string Status of Lead Inquiry
     */
    private $leadStatus;

    /**
     * @var string Contact Type of Lead Inquiry
     */
    private $contactType;

    /**
     * @var int Sales Person ID Request During Lead Inquiry
     */
    private $salesPersonId;


    public static function getFromLead() {
        
    }


    /**
     * Get Inquiry Type
     * 
     * @return string
     */
    private function getInquiryType() {
        // Get Type
        if(!in_array($this->inquiryType, self::INQUIRY_TYPES)) {
            $this->inquiryType = self::INQUIRY_TYPE_DEFAULT;
        }

        // Set New Type
        return $this->inquiryType;
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
     * Is Trailer Trader?
     * 
     * @return bool
     */
    public function isTrailerTrader(): bool
    {
        return $this->websiteDomain === self::TT_DOMAIN;
    }


    /**
     * Decode Metadata and Convert Into Array
     * 
     * // DEALER SITES ONLY
     * @return array{'contact-address': array,
     *               'adf-contact-address': null,
     *               'subject': string,
     *               'domain': string,
     *               'POST_DATA': array,
     *               'COOKIE_DATA': array,
     *               'SERVER_DATA': array,
     *               'SPAM_SCORE': int?,
     *               'SPAM_FAILURES': array?,
     *               'IS_DEV': bool?,
     *               'REAL_TO': array,
     *               'adf-contact-address': string}
     * 
     * // JOTFORM ONLY
     * @return array{'jotformId': int, 'submissionId': int}
     */
    public function getMetadata() {
        return json_decode($this->metadata);
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

    /**
     * Get Email BG Color
     * 
     * @return string
     */
    public function getBgColor() {
        return $this->isTrailerTrader() ? self::TT_EMAIL_BODY : self::DEFAULT_EMAIL_BODY;
    }

    /**
     * Get Email Header BG Color
     * 
     * @return string
     */
    public function getHeaderBgColor() {
        return $this->isTrailerTrader() ? self::TT_EMAIL_HEADER : self::DEFAULT_EMAIL_HEADER;
    }
}