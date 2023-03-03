<?php

namespace App\Services\Marketing\Craigslist\DTOs;

use App\Services\Marketing\Craigslist\DTOs\ClappQueue;
use App\Models\Marketing\Craigslist\Session;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class ClappForm
 * 
 * @package App\Services\Marketing\Craigslist\DTOs
 */
class ClappForm
{
    use WithConstructor, WithGetter;


    /**
     * @const Country United States
     */
    const COUNTRY_USA = 'us';

    /**
     * @const Country Canada
     */
    const COUNTRY_CANADA = 'ca';


    /**
     * @const Default Language
     */
    const LANGUAGE_DEFAULT = 5;


    /**
     * @var ClappQueue
     */
    private $queueData;

    /**
     * @var Session
     */
    private $session;


    /**
     * @var string
     */
    private $fromEmail;

    /**
     * @var string
     */
    private $confirmEmail;

    /**
     * @var int
     */
    private $language;


    /**
     * @var string
     */
    private $privacy;

    /**
     * @var int (0 or 1)
     */
    private $showPhoneOk;

    /**
     * @var string (Y | N)
     */
    private $seeMyOther;

    /**
     * @var string (Y | N)
     */
    private $wantAMap;


    /**
     * @var string
     */
    private $contactName;

    /**
     * @var int (0 or 1)
     */
    private $contactPhoneOk;

    /**
     * @var string
     */
    private $contactPhoneExt;


    /**
     * @var string
     */
    private $postingTitle;

    /**
     * @var string
     */
    private $postingBody;

    /**
     * @var string
     */
    private $ask;

    /**
     * @var string
     */
    private $condition;


    /**
     * @var string
     */
    private $crossStreet1;

    /**
     * @var string
     */
    private $crossStreet2;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $region;

    /**
     * @var string
     */
    private $postal;


    /**
     * @var string
     */
    private $vin;

    /**
     * @var int
     */
    private $year;

    /**
     * @var string
     */
    private $color;

    /**
     * @var int
     */
    private $miles;

    /**
     * @var string
     */
    private $fuelType;

    /**
     * @var int
     */
    private $length;

    /**
     * @var int
     */
    private $overallLength;

    /**
     * @var int
     */
    private $propulsion;


    /**
     * Create ClappQueue From Session/Queue Data
     * 
     * @param Session $session
     * @return ClappPost
     */
    public static function fill(Session $session): ClappPost {
        // Create ClappQueue From Session/Queue
        $qData = ClappQueue::fill($session);

        // Create ClappForm Results
        return new ClappForm([
            'queue_data' => $qData,
            'session' => $session,
            'category' => $session->inventory->category,
            'category_type' => $session->profile->category->grouping,
            'subarea' => strtolower($session->profile->subarea_alt_name),
            'neighborhood' => strtolower($qData->geographicArea),
            'market' => $this->profile->city_code,
            'from_email' => $session->profile->craigslist_user,
            'confirm_email' => $session->profile->craigslist_user,
            'posting_title' => $qData->title,
            'privacy' => $session->profile->cl_privacy,
            'ask' => floor($qData->price),
            'show_phone_ok' => $qData->hasPhone(),
            'contact_name' => $qData->trimmedContactName(),
            'contact_phone_ok' => $qData->hasPhone(),
            'contact_phone_ext' => '',
            'geographic_area' => $qData->location,
            'cross_street1' => $session->profile->map_street,
            'cross_street2' => $session->profile->map_cross_street,
            'city' => $session->profile->map_city,
            'region' => $session->profile->map_state,
            'country' => $session->profile->country,
            'postal' => $qData->postal,
            'posting_body' => $qData->trimmedBody(),
            'language' => self::LANGUAGE_DEFAULT,
            'condition' => $this->clCondition($session->inventory->condition),
            'see_my_other' => $session->profile->show_more_ads ? 'Y' : 'N',
            'want_a_map' => $session->profile->use_map ? 'Y' : 'N',
            'vin' => $session->inventory->vin,
            'year' => $session->inventory->year,
            'color' => $session->inventory->attributes['color'] ?? '',
            'miles' => $session->inventory->attributes['mileage'] ?? '',
            'fuel_type' => $session->inventory->attributes['fuel_type'] ?? '',
            'length' => $session->inventory->length,
            'overall_length' => $session->inventory->attributes['overall_length'] ?? 0,
            'propulsion' => $session->inventory->attributes['propulsion'] ?? ''
        ]);
    }


    /**
     * Get Formatted Postal Code
     * 
     * @return string
     */
    public function postal(): string {
        // Clean Up Zip Code
        $postal = preg_replace('/[^0-9]/m', '', $this->qData->postal);

        // Is This Canadian?!
        if($this->country === self::COUNTRY_CANADA) {
            return preg_replace("/([a-z0-9]{3})([a-z0-9]{3})/i", "$1 $2", $postal);
        }

        // Return Unformatted Postal Code
        return substr($postal, 0, 5);
    }

    /**
     * Get Formatted Make + Model
     * 
     * @return string
     */
    public function makeModel(): string {
        return $this->clTruncate($this->qData->make . ' ' . $this->qData->model, 32);
    }


    /**
     * RV Type
     * 
     * @return string
     */
    public function rvType(): string {
        // RV Type By Category
        return $this->clRvType($this->category);
    }

    /**
     * RV Fuel Type
     * 
     * @return string
     */
    public function rvFuelType(): string {
        // Return Transmission
        if(in_array($this->category, array('class_a', 'class_b', 'class_c'))) {
            return $this->clFuelType($this->fuelType, 'rv');
        }

        // Other Fuel Type
        return $this->clFuelType('other', 'rv');
    }

    /**
     * RV Transmission
     * 
     * @return string
     */
    private function rvTransmission(): string {
        // Return Transmission
        if(in_array($this->category, array('class_a', 'class_b', 'class_c'))) {
            return '2';
        }

        // Other Transmission
        return '3';
    }


    /**
     * Get Boat Length
     * 
     * @return int
     */
    private function boatLength(): int {
        // Get Overall Length
        if($this->overallLength) {
            return floor($this->overallLength);
        }

        // Return Length
        return $this->length;
    }

    /**
     * Get Propulsion Type
     * 
     * @return int
     */
    private function boatPropulsion(): int {
        // Propulsion Set on Inventory?
        if($this->propulsion) {
            if($inventory['attributes']['propulsion'] === 'other') {
                return 3;
            } elseif($inventory['attributes']['propulsion'] === 'sail') {
                return 1;
            }
        }

        // Determine From Category
        switch($this->category) {
            // Human/Oar Boat
            case "canoe-kayak":
            case "inflatable":
                return 3;
            break;

            // Sail Boat
            case "sailboat":
                return 1;
            break;
        }

        // Return Default Category
        return 2;
    }
}