<?php

namespace App\Jobs\Integration\Facebook\Catalog;

use App\Exceptions\Integration\Facebook\EmptyCatalogPayloadListingsException;
use App\Exceptions\Integration\Facebook\FailedCreateTempCatalogCsvException;
use App\Exceptions\Integration\Facebook\MissingCatalogFeedPathException;
use App\Jobs\Job;
use App\Models\Inventory\Inventory;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class HomeJob extends Job
{
    use Dispatchable;

    /**
     * Facebook Availability
     */
    const AVAILABLE = 'for_sale';
    const UNAVAILABLE = 'off_market';
    const PENDING = 'sale_pending';

    /**
     * @const Property Types
     */
    const CABIN = 'Cabin';
    const COTTAGE = 'Cottage';
    const BARN = 'Barn';
    const GARAGE = 'Garage/Carport';
    const VINYL = 'Vinyl';
    const METAL = 'Metal Building';
    const SHED_UTILITY = 'Utility Shed';
    const SHED_METRO = 'Metro Shed';
    const SHED_STEEL = 'Steel Frame Shed';
    const OTHER = 'Other Building';

    /**
     * Property Type Map
     *
     * @var type 
     */
    const PROPERTY_MAP = array(
        'cabin' => self::CABIN,
        'cottage' => self::COTTAGE,
        'barn' => self::BARN,
        'garage' => self::GARAGE,
        'vinyl' => self::VINYL,
        'metal_building' => self::METAL,
        'utility_shed' => self::SHED_UTILITY,
        'metro_shed' => self::SHED_METRO,
        'steel_frame_shed' => self::SHED_STEEL,
        'other' => self::OTHER,
        '' => self::OTHER
    );

    /**
     * Facebook Catalog CSV Columns
     *
     * @var type 
     */
    private $csvColumns = array(
        'fb_page_id',
        'home_listing_id',
        'name',
        'description',
        'url',
        'year_built',
        'images',
        'price',
        'availability',
        'product_type',
        'num_beds',
        'address'
    );


    /**
     * @var string $feedPath
     */
    private $feedPath;

    /**
     * @var array $integration
     */
    private $integration;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\stdclass $integration, string $feedPath)
    {
        // Set Feed Path/Integration to Process
        $this->feedPath = $feedPath;
        $this->integration = $integration;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Integration Empty?
        if(empty($this->integration) || empty($this->integration->listings)) {
            // We shouldn't be here if the integration has no listings, but throw an error just in case!
            throw new EmptyCatalogPayloadListingsException;
        }

        // No Feed URL?
        if(empty($this->feedPath)) {
            throw new MissingCatalogFeedPathException;
        }

        // Create Filename With Headers
        $file = $this->createCsv();

        // Process Integration
        foreach($this->integration->listings as $listing) {
            try {
                $this->insertCsvRow($file, $listing);
            } catch(\Exception $e) {
                Log::error("Exception returned processing listing #" . $listing->vehicle_id .
                            " on catalog # " . $this->integration->catalog_id . "; " . 
                            $e->getMessage() . ": " . $e->getTraceAsString());
            }
        }

        // Store Final CSV
        return $this->storeCsv($file, $this->feedPath);
    }


    /**
     * Create Temp CSV With Headers
     * 
     * @return File
     */
    private function createCsv() {
        // Create File
        $file = fopen('php://temp/maxmemory:1048576', 'w');
        if($file === FALSE) {
            throw new FailedCreateTempCatalogCsvException;
        }

        // Add Headers
        fputcsv($file, $this->csvColumns);

        // Return Temp CSV File
        return $file;
    }

    /**
     * Insert CSV Row
     * 
     * @return File
     */
    private function insertCsvRow($file, $listing) {
        // Clean Up Results
        $clean = $this->cleanCsvRow($listing);

        // Skip if Fields Missing
        if(empty($clean->name) || empty($clean->description)) {
            return false;
        }

        // Create Row
        $row = array();
        foreach($this->csvColumns as $k => $column) {
            $row[$k] = '';
            if(isset($clean->{$column}) && $clean->{$column} !== null) {
                $row[$k] = $clean->{$column};
            }
        }
        ksort($row);

        // Add Cleaned Results to CSV
        fputcsv($file, $row);

        // Return Result
        return $file;
    }

    /**
     * Get Cleaned CSV Row
     * 
     * @param array $listing
     * @return array of cleaned listing results
     */
    private function cleanCsvRow($listing) {
        // Get Inventory URL
        $listing->url = $this->getInventoryUrl($listing->home_listing_id);

        // Encode Images
        if(is_array($listing->image)) {
            $listing->image = json_encode($listing->image);
        }

        // Fix Availability
        if($listing->availability === '4') {
            $listing->availability = self::PENDING;
        } else {
            $listing->availability = self::AVAILABLE;
        }

        // Property Type
        $listing->property_type = $this->mapPropertyType($listing->property_type);

        // Handle Address
        $listing->address = json_encode([
            'addr1' => $listing->address_addr1,
            'city' => $listing->address_city,
            'region' => $listing->address_region,
            'country' => $listing->address_country,
            'postal' => $listing->address_postal,
            'latitude' => $listing->address_latitude,
            'longitude' => $listing->address_longitude
        ]);

        // Append Description
        $listing->description = isset($listing->description) ? trim($listing->description) : '';
        if($listing->real_dealer_id == 8757 && !empty($listing->description)) {
            $listing->description .= 'In some cases, pricing may not include freight, prep, doc/title fees, additional equipment, or sales tax';
        }

        // Return Cleaned Up Listing Array
        return $listing;
    }

    /**
     * Stores CSV on S3 and returns its URL
     *
     * @param File $file temporary file to store results for
     * @param string $filePath full filename path on S3
     * @return string
     */
    private function storeCsv($file, $filePath) {
        // Get Temp File Contents
        rewind($file);
        $csv = stream_get_contents($file);
        fclose($file); // releases the memory (or tempfile)

        // Return Stored File
        return Storage::disk('s3')->put($filePath, $csv, 'public');
    }


    /**
     * Get Inventory URL From Inventory ID
     * 
     * @param int $inventoryId
     * @return string direct link to exact domain inventory URL
     */
    private function getInventoryUrl($inventoryId) {
        // Get Inventory Item for Vehicle
        $inventory = Inventory::find($inventoryId);

        // Website Domain Exists?
        if(!empty($inventory->user->website->domain)) {
            $url = $inventory->getUrl();

            // Domain/URL Exists?
            if(!empty($url)) {
                return 'https://' . $inventory->user->website->domain . $url;
            }
        }

        // Return Empty URL
        return '';
    }

    /**
     * Map Property Type Based on Category
     * 
     * @param string $cat
     * @return string final vehicle type
     */
    private function mapPropertyType($cat) {
        // Set Default Type
        $type = self::OTHER;

        // Check Mapping
        if(isset($this->propertyMap[$cat])) {
            $type = $this->propertyMap[$cat];
        }

        // Return Result
        return $type;
    }
}
