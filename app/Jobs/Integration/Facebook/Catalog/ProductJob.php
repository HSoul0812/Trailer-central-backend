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

class ProductJob extends Job
{
    use Dispatchable;

    /**
     * Specific Data Types
     */
    const TC_PRIVACY_POLICY_URL = 'https://trailercentral.com/privacy-policy/';

    /**
     * Facebook Vehicle Types
     */
    const BOAT = 'BOAT';
    const CAR = 'CAR_TRUCK';
    const COMMERCIAL = 'COMMERCIAL';
    const CYCLE = 'MOTORCYCLE';
    const SPORT = 'POWERSPORT';
    const CAMPER = 'RV_CAMPER';
    const TRAILER = 'TRAILER';

    /**
     * Facebook Body Styles
     */
    const CONVERTIBLE = 'CONVERTIBLE';
    const COUPE = 'COUPE';
    const CROSSOVER = 'CROSSOVER';
    const HATCHBACK = 'HATCHBACK';
    const MINIVAN = 'MINIVAN';
    const TRUCK = 'TRUCK';
    const SEDAN = 'SEDAN';
    const SMALL = 'SMALL_CAR';
    const SUV = 'SUV';
    const VAN = 'VAN';
    const WAGON = 'WAGON';
    const OTHER = 'OTHER';

    /**
     * Facebook Availability
     */
    const AVAILABLE = 'in stock';
    const UNAVAILABLE = 'out of stock';
    const ONORDER = 'on order';


    /**
     * Facebook Catalog CSV Columns
     *
     * @var type 
     */
    private $csvColumns = array(
        'id',
        'title',
        'description',
        'rich_text_description',
        'availability',
        'condition',
        'visibility',
        'price',
        'sale_price',
        'link',
        'image_link',
        'additional_image_link',
        'brand',
        'color',
        'material',
        'product_type',
        'google_product_category'
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
                            " on catalog # " . $this->interaction->catalog_id . "; " . 
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
        if(empty($clean->title) || empty($clean->brand)) {
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
        $link = $this->getInventoryUrl($listing->id);
        if(!empty($link)) {
            $listing->link = $link;
        }

        // Encode Images
        if(is_array($listing->image)) {
            $listing->image = json_encode($listing->image);
        }


        // Append Description
        $listing->description = isset($listing->description) ? trim($listing->description) : '';
        if($listing->dealer_id == 8757 && !empty($listing->description)) {
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
}
