<?php

namespace App\Services\Dispatch\Craigslist\DTOs;

use App\Models\Marketing\Craigslist\Queue;
use App\Services\Dispatch\Craigslist\DTOs\ClappQueue;
use App\Traits\Marketing\CraigslistHelper;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class ClappPost
 * 
 * @package App\Services\Dispatch\Craigslist\DTOs
 */
class ClappPost
{
    use WithConstructor, CraigslistHelper, WithGetter;


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
     * @const Manage URL Prefix
     */
    const MANAGE_URL = 'https://post.craigslist.org/manage/';


    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var ClappQueue
     */
    private $qData;


    /**
     * @var string
     */
    private $initUrl;

    /**
     * @var string
     */
    private $category;

    /**
     * @var string
     */
    private $categoryType;

    /**
     * @var string
     */
    private $subarea;

    /**
     * @var string
     */
    private $market;


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
     * @var string
     */
    private $contactPhone;

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
    private $geographicArea;

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
    private $country;


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
     * @param Queue $queue
     * @return ClappPost
     */
    public static function fill(Queue $queue): ClappPost {
        // Create ClappQueue From Session/Queue
        $qData = ClappQueue::fill($queue);

        // Create ClappPost Results
        return new ClappPost([
            'queue' => $queue,
            'q_data' => $qData,
            'init_url' => $queue->profile->initial_url,
            'category' => $queue->inventory->category,
            'category_type' => $queue->profile->category->grouping,
            'subarea' => strtolower($queue->profile->market->subarea_alt_name),
            'market' => $queue->profile->market_city,
            'from_email' => $queue->profile->Username,
            'confirm_email' => $queue->profile->Username,
            'posting_title' => $qData->title,
            'privacy' => $queue->profile->cl_privacy,
            'ask' => floor($qData->price),
            'show_phone_ok' => $qData->hasPhone(),
            'contact_name' => $qData->trimmedContactName(),
            'contact_phone' => $qData->phone,
            'contact_phone_ok' => $qData->hasPhone(),
            'contact_phone_ext' => '',
            'geographic_area' => strtolower($qData->location),
            'cross_street1' => $queue->profile->map_street,
            'cross_street2' => $queue->profile->map_cross_street,
            'city' => $queue->profile->map_city,
            'region' => $queue->profile->map_state,
            'country' => $queue->profile->country,
            'postal' => $qData->postal,
            'posting_body' => $qData->trimmedBody(),
            'language' => self::LANGUAGE_DEFAULT,
            'condition' => $queue->inventory->condition,
            'see_my_other' => $queue->profile->show_more_ads ? 'Y' : 'N',
            'want_a_map' => $queue->profile->use_map ? 'Y' : 'N',
            'vin' => $queue->inventory->vin,
            'year' => $queue->inventory->year,
            'color' => $queue->inventory->attributes['color'] ?? '',
            'miles' => $queue->inventory->attributes['mileage'] ?? '',
            'fuel_type' => $queue->inventory->attributes['fuel_type'] ?? '',
            'length' => $queue->inventory->length,
            'overall_length' => $queue->inventory->attributes['overall_length'] ?? 0,
            'propulsion' => $queue->inventory->attributes['propulsion'] ?? ''
        ]);
    }


    /**
     * Get Params
     * 
     * @return array
     */
    public function getParams(): array {
        return [
            'queue_id' => $this->queue->queue_id,
            'parameter' => $this->qData->json(),
            'costs' => $this->qData->costs
        ];
    }

    /**
     * Get Preview
     * 
     * @return string json<array{title: string,
     *                           price: float,
     *                           body: string,
     *                           area: string,
     *                           subarea: string,
     *                           section: string,
     *                           category: string,
     *                           hood: string,
     *                           condition: string,
     *                           make_manu: string,
     *                           model: string,
     *                           images: int}>
     */
    public function preview(): string {
        return $this->clEncodeJson([
            'title' => $this->postingTitle,
            'price' => $this->ask,
            'body' => $this->qData->trimmedBody(),
            'area' => $this->market,
            'subarea' => $this->subarea,
            'section' => 'for sale',
            'category' => $this->category,
            'hood' => $this->geographicArea,
            'condition' => $this->condition,
            'make_manu' => $this->qData->make,
            'model' => $this->qData->model,
            'images' => count($this->qData->images)
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
     * Get Condition
     * 
     * @return string
     */
    public function condition(): string {
        return $this->clCondition($this->condition);
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