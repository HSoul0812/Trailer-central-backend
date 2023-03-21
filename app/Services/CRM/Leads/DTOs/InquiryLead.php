<?php

namespace App\Services\CRM\Leads\DTOs;

use App\Models\CRM\Leads\Lead;
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
    const INQUIRY_SPAM_TO = [
        ['email' => 'alberto@trailercentral.com']
    ];

    /**
     * @const array
     */
    const INQUIRY_BCC_TO = [
        ['email' => 'bcc@trailercentral.com'],
        ['email' => 'alberto@trailercentral.com']
    ];

    /**
     * @const array
     */
    const INQUIRY_DEV_TO = [
        ['email' => 'ben+dev-contact-forms@trailercentral.com'],
        ['email' => 'judson@trailercentral.com'],
        ['email' => 'david@trailercentral.com'],
        ['email' => 'tuan@trailercentral.com'],
    ];


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
    const TT_SIMPLE_DOMAIN = 'https://trailertrader.com';

    /**
     * @const string
     */
    const TT_EMAIL_BODY = '#ffffff';

    /**
     * @const string
     */
    const TT_EMAIL_HEADER = '#F8F9FA';


    /**
     * @const string
     */
    const INQUIRY_TYPE_DEFAULT = 'general';

    /**
     * @const array
     */
    const INQUIRY_TYPES = [
        'general',
        'cta',
        'inventory',
        'part',
        'showroom',
        'call',
        'sms',
        'bestprice'
    ];

    /**
     * @const array
     */
    const INQUIRY_EMAIL_TYPES = [
        'general',
        'cta',
        'inventory',
        'part',
        'showroom',
        'call',
        'bestprice',
    ];

    /**
     * @const array
     */
    const NON_INVENTORY_TYPES = ['part', 'showroom'];


    /**
     * @const int
     */
    const MERGE_MATCH_COUNT = 2;



    /**
     * @var int Dealer ID for Lead Inquiry
     */
    private $dealerId;

    /**
     * @var int Dealer Location ID for Lead Inquiry
     */
    private $dealerLocationId;

    /**
     * @var int Website ID for Lead Inquiry
     */
    private $websiteId;

    /**
     * @var int Website Domain for Lead Inquiry
     */
    private $websiteDomain;

    /**
     * @var string Device Lead Inquiry Was Sent From
     */
    private $device;


    /**
     * @var string Type of Lead Inquiry
     */
    private $inquiryType;

    /**
     * @var string Email of Lead Inquiry
     */
    private $inquiryEmail;

    /**
     * @var string Name of Lead Inquiry
     */
    private $inquiryName;

    /**
     * @var string Cookie Session ID for Lead Inquiry
     */
    private $cookieSessionId;


    /**
     * @var string Logo of Lead Inquiry
     */
    private $logo;

    /**
     * @var string Logo URL of Lead Inquiry
     */
    private $logoUrl;

    /**
     * @var string From Name of Lead Inquiry
     */
    private $fromName;

    /**
     * @var string Simple TrailerTrader Domain Name
     */
    private $simpleDomain;

    /**
     * @var array<string> Lead Types to Insert to Lead Inquiry
     */
    private $leadTypes;

    /**
     * @var array<string> Units of Interest for the Lead Inquiry
     */
    private $inventory;

    /**
     * @var ?int Primary Item ID for Lead Inquiry
     */
    private $itemId;


    /**
     * @var string Title of Lead Form / Name of Inventory Item Associated with Lead Inquiry
     */
    private $title;

    /**
     * @var string Referral of Lead Inquiry
     */
    private $referral;

    /**
     * @var ?string URL Directly to Original Lead Inquiry
     */
    private $url;

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
     * @var string Preferred Contact for Lead Inquiry
     */
    private $preferredContact;


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
     * @var bool Is This Lead Inquiry From Classifieds?
     */
    private $isFromClassifieds;


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


    /**
     * Get Inquiry Type
     *
     * @return string
     */
    public function getInquiryType(): string {
        // Get Type
        if(!in_array($this->inquiryType, self::INQUIRY_TYPES)) {
            $this->inquiryType = self::INQUIRY_TYPE_DEFAULT;
        }

        // Set New Type
        return $this->inquiryType;
    }

    /**
     * Get Inquiry View
     *
     * @return string
     */
    public function getInquiryView(): string {
        return ($this->inquiryType === 'cta') ? 'general' : $this->inquiryType;
    }

    /**
     * Get Unit Type
     *
     * @return string
     */
    public function getUnitType(): string {
        // Get Type
        $type = $this->getInquiryType();

        // Set New Type
        return ($type === 'showroomModel' ? 'showroom' : $type);
    }

    /**
     * Get Inquiry URL
     *
     * @return string
     */
    public function getInquiryUrl(): string {
        if (!empty($this->url) && str_starts_with($this->url, 'http')) {
            return $this->url;
        }

        if (!empty($this->referral) && str_starts_with($this->referral, 'http')) {
            return $this->referral;
        }

        // Only Append If Domain Exists
        if($this->websiteDomain) {
            // Make sure it has only one '/' on the right side of the URL
            $websiteDomain = rtrim($this->websiteDomain, "/") . '/';

            // Return URL With Website Domain
            if(!empty($this->url)) {
                // Make sure there is no '/' on the left
                $url = ltrim($this->url, "/");

                return $websiteDomain . $url;
            }

            // Return Referral URL Instead
            if(!empty($this->referral)) {
                // Make sure there is no '/' on the left
                $referral = ltrim($this->referral, "/");

                return $websiteDomain . $referral;
            }
        }

        // Return Blank
        return '';
    }



    /**
     * Get Inquiry To
     *
     * @return string
     */
    public function getInquiryTo(): string {
        // Get Inquiry
        if(is_array($this->inquiryEmail)) {
            return implode(";", $this->inquiryEmail);
        }
        return $this->inquiryEmail ?? '';
    }

    /**
     * Get Inquiry To Array
     *
     * @return array{array{name: string, email: string}, ...etc}
     */
    public function getInquiryToArray(): array {
        // If Dev, Only Return Specific Entries
        if(!empty($this->isDev())) {
            $to = self::INQUIRY_DEV_TO;
        }
        // If Spam, Only Return Spam
        elseif(!empty($this->isSpam)) {
            $to = self::INQUIRY_SPAM_TO;
        }
        // Normal, Return Proper Inquiry
        else {
            // Get To Emails
            $to = [];
            if(is_array($this->inquiryEmail)) {
                foreach($this->inquiryEmail as $toEmail) {
                    $to[] = ['name' => $this->inquiryName, 'email' => trim($toEmail)];
                }
            } else {
                if (strpos($this->inquiryEmail, ';') !== false) {
                    $toemails = explode(';', $this->inquiryEmail);
                    foreach($toemails as $toemail) {
                        $to[] = ['name' => $this->inquiryName, 'email' => trim($toemail)];
                    }
                } else if (strpos($this->inquiryEmail, ',') !== false) {
                    $toemails = explode(',', $this->inquiryEmail);
                    foreach($toemails as $toemail) {
                        $to[] = ['name' => $this->inquiryName, 'email' => trim($toemail)];
                    }
                } else {
                    $to[] = ['name' => $this->inquiryName, 'email' => trim($this->inquiryEmail)];
                }

            }

            // Append for TT
            if ($this->websiteDomain == self::TT_SIMPLE_DOMAIN) {
                $to[] = ['name' => $this->firstName, 'email' => trim($this->emailAddress)];
            }
            return $to;
        }

        // Return With Merged CC To
        return array_merge($to, self::INQUIRY_BCC_TO);
    }

    /**
     * Get Inquiry BCC Array
     *
     * @return array{array{name: string, email: string}, ...etc}
     */
    public function getInquiryBccArray(): array {
        // If Dev, Only Return Specific Entries
        if(empty($this->isDev()) && empty($this->isSpam)) {
            return self::INQUIRY_BCC_TO;
        }
        return [];
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
     * @return string $this->preferredContact || 'phone' if phone exists || 'email' otherwise
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
        return $this->websiteDomain === self::TT_SIMPLE_DOMAIN;
    }

    /**
     * Is Dev?
     *
     * @return bool
     */
    public function isDev(): bool
    {
        // Get Metadata
        $metadata = $this->getMetadata();

        // Return
        return !empty($metadata['IS_DEV']);
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
    public function getMetadata(): array {
        // Get JSON
        if(is_string($this->metadata)) {
            $json = json_decode($this->metadata, true);
        } else {
            $json = $this->metadata;
        }

        // Return Result
        if(!empty($json)) {
            return $json;
        }
        return [];
    }


    /**
     * Build Subject
     *
     * @return string
     */
    public function getSubject(): string {
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
    public function getBgColor(): string {
        return $this->isTrailerTrader() ? self::TT_EMAIL_BODY : self::DEFAULT_EMAIL_BODY;
    }

    /**
     * Get Email Header BG Color
     *
     * @return string
     */
    public function getHeaderBgColor(): string {
        return $this->isTrailerTrader() ? self::TT_EMAIL_HEADER : self::DEFAULT_EMAIL_HEADER;
    }

    /**
     * Get Admin Message for Inquiry Email
     *
     * @return array{allFailures: string,
     *               remoteAddr: string,
     *               forwardedFor: string,
     *               originalContactList: string,
     *               resendUrl: string}
     */
    public function getAdminMsg(): array {
        // Get Meta Data
        $metadata = $this->getMetadata();

        // Define Spam Data
        return [
            'isSpam'              => $this->isSpam,
            'allFailures'         => !empty($metadata['SPAM_FAILURES']) ? implode(", ", $metadata['SPAM_FAILURES']) : '',
            'remoteAddr'          => $metadata['REMOTE_ADDR'] ?? '',
            'forwardedFor'        => $metadata['FORWARDED_FOR'] ?? '',
            'originalContactList' => !empty($metadata['ORIGINAL_RECIPIENTS']) ? implode('; ', $metadata['ORIGINAL_RECIPIENTS']) : '',
            'resendUrl'           => $metadata['REMAIL_URL'] ?? ''
        ];
    }


    /**
     * Get Email Vars For Inquiry Email Templates
     *
     * @return array{year: int,
     *               bgColor: string,
     *               bgHeader: string,
     *               inquiryType: string,
     *               inquiryView: string,
     *               inquiryName: string,
     *               inquiryEmail: string,
     *               logo: string,
     *               logoUrl: string,
     *               fromName: string,
     *               subject: string,
     *               website: string,
     *               title: string,
     *               stock: string,
     *               url: string,
     *               fullName: string,
     *               isSpam: bool,
     *               allFailures: string,
     *               remoteAddr: string,
     *               forwardedFor: string,
     *               originalContactList: string,
     *               resendUrl: string}
     */
    public function getEmailVars(): array {
        // Return Inquiry Email Vars
        return array_merge([
            'year'             => date('Y'),
            'bgColor'          => $this->getBgColor(),
            'bgHeader'         => $this->getHeaderBgColor(),
            'inquiryView'      => $this->getInquiryView(),
            'logo'             => $this->logo,
            'logoUrl'          => $this->logoUrl,
            'fromName'         => $this->fromName,
            'subject'          => $this->getSubject(),
            'website'          => $this->websiteDomain,
            'device'           => $this->device,
            'title'            => $this->title,
            'stock'            => $this->stock,
            'url'              => $this->getInquiryUrl(),
            'fullName'         => $this->getFullName(),
            'email'            => $this->emailAddress,
            'phone'            => $this->phoneNumber,
            'postal'           => $this->zip,
            'preferred'        => $this->getPreferredContact(),
            'comments'         => $this->comments,
            'simpleDomain'     => self::TT_SIMPLE_DOMAIN
        ], $this->getAdminMsg());
    }


    /**
     * Find Matches to Existing Lead
     *
     * @param Lead $lead
     * @return int 0-3
     */
    public function findMatches(Lead $lead): int {
        // Initialize Matches
        $matches = 0;

        // Matched Phone?
        $phone1 = preg_replace("/[-+)( x]+/", "", $this->phoneNumber);
        $phone2 = '1' . $phone1;
        if(strlen($phone1) === 11) {
            $phone2 = substr($phone1, 1);
        }
        if($phone1 === $lead->clean_phone || $phone2 === $lead->clean_phone) {
            $matches++;
        }

        // Matched Email?
        if($this->emailAddress === $lead->email_address) {
            $matches++;
        }

        // Matched Name?
        if(strtolower($this->firstName) === strtolower($lead->first_name) && strtolower($this->lastName) === strtolower($lead->last_name)) {
            $matches++;
        }

        // Return
        return $matches;
    }
}
