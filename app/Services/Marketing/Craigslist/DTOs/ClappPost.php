<?php

namespace App\Services\Marketing\Craigslist\DTOs;

use App\Models\Marketing\Craigslist\ActivePost;
use App\Models\Marketing\Craigslist\Post;
use App\Models\Marketing\Craigslist\Sync;
use App\Repositories\Marketing\Craigslist\CityRepositoryInterface;
use App\Repositories\Marketing\Craigslist\SubareaRepositoryInterface;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class Client
 * 
 * @package App\Services\Marketing\Craigslist\DTOs
 */
class ClappPost
{
    use WithConstructor, WithGetter;

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $clid;

    /**
     * @var int
     */
    private $dealerId;

    /**
     * @var int
     */
    private $inventoryId;

    /**
     * @var int
     */
    private $profileId;

    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var int
     */
    private $queueId;


    /**
     * @var string
     */
    private $title;

    /**
     * @var float
     */
    private $price;

    /**
     * @var string
     */
    private $category;

    /**
     * @var string
     */
    private $area;

    /**
     * @var string
     */
    private $subarea;

    /**
     * @var string
     */
    private $status;


    /**
     * @var string
     */
    private $viewUrl;

    /**
     * @var string
     */
    private $manageUrl;

    /**
     * @var ClappQueue
     */
    private $queue;

    /**
     * @var string
     */
    private $posted;

    /**
     * @var string
     */
    private $added;

    /**
     * @var string
     */
    private $drafted;


    /**
     * Create ClappPost From ActivePost
     * 
     * @param ActivePost $post
     * @param Sync $sync
     * @return ClappPost
     */
    public static function fromActivePost(ActivePost $post, Sync $sync): ClappPost {
        // Create ClappPost From ActivePost
        $clappPost = new ClappPost([
            'clid' => $post->clid,
            'dealer_id' => $post->dealer_id,
            'inventory_id' => $post->inventory_id,
            'profile_id' => $post->profile_id,
            'session_id' => $post->session_id,
            'queue_id' => $post->queue_id,
            'title' => $post->title,
            'price' => $post->price,
            'category' => $post->category,
            'area' => $post->area,
            'subarea' => $post->subarea,
            'status' => $post->status,
            'view_url' => $post->view_url,
            'manage_url' => $post->manage_url,
            'queue' => $post->queue ? ClappQueue::fill($post->queue) : null
        ]);

        // Clean ClappPost
        $clappPost->clean($sync);

        // Return Clapp Post
        return $clappPost;
    }

    /**
     * Create ClappPost From Post
     * 
     * @param Post $post
     * @param Sync $sync
     * @return ClappPost
     */
    public static function fromPost(Post $post, Sync $sync): ClappPost {
        // Create ClappPost From Post
        $clappPost = new ClappPost([
            'clid' => $post->clid,
            'dealer_id' => $post->dealer_id,
            'inventory_id' => $post->inventory_id,
            'profile_id' => $post->profile_id,
            'session_id' => $post->session_id,
            'queue_id' => $post->queue_id,
            'title' => $post->title,
            'price' => $post->price,
            'category' => $post->category,
            'area' => $post->area,
            'subarea' => $post->subarea,
            'status' => $post->status,
            'view_url' => $post->view_url,
            'manage_url' => $post->manage_url,
            'queue' => $post->queue ? ClappQueue::fill($post->queue) : null
        ]);

        // Return Clean ClappPost
        return $clappPost->clean($sync);
    }


    /**
     * Clean Post Data w/Active Post
     *
     * @param Sync $sync
     * @return ClappPost
     */
    public function clean(Sync $sync): ClappPost {
        // Insert CLID if Missing
        if(empty($this->clid)) {
            $this->clid = $sync->clid;
        }

        // Fix URL's
        $this->viewUrl = $sync->viewUrl;
        $this->manageUrl = $sync->manageUrl;

        // Fix Status
        $this->status = 'removed';
        if(!empty($sync->status)) {
            $this->status = preg_replace("/[^A-Za-z0-9]/", '', strip_tags($sync->status));
        }

        // Clean Category
        $this->cleanArea($sync);

        // Clean Title
        $this->cleanTitle($sync);

        // Clean Area Category
        $this->cleanCat($sync);

        // Clean Subarea
        $this->cleanSubarea();

        // Clean Price
        $this->cleanPrice();

        // Clean Dates
        $this->cleanDates();

        // Return Clapp Post
        return $this;
    }


    /**
     * Clean Area
     * 
     * @param Sync $sync
     * @return string
     */
    private function cleanArea(Sync $sync): string {
        // Compare Category
        $cat = trim(preg_replace("/<abbr title=\".*?\">(.*?)<\/abbr>/", '$1', $sync->cat));

        // Check for Hyphen
        if(strpos($cat, '-') !== -1) {
            $cats = explode("-", $cat);
            if (count($cats) > 1) {
                $cat = trim(reset($cats));
            }
        }

        // Cat More than 3 Characters?
        if(strlen($cat) > 3) {
            $cat = substr($cat, 0, 3);
        }

        // Get City Details
        $city = app()->make(CityRepositoryInterface)->get(['id' => $sync->cat]);

        // Get Posting City From Cat
        $this->log->debug('Retrieved area to search in: "' . $city->name . '" and "' .
                            $city->alt_name . '" and "' . $city->preview_name .'" and "' .
                            $city->city . '", from cat: "' . $sync->cat . '"');

        // Return Area
        $this->area = $city->name;
        $this->timezone = $city->timezone;
        return $this->area;
    }

    /**
     * Clean Up Titles & Price
     * 
     * @param Sync $sync
     * @return string
     */
    private function cleanTitle(Sync $sync): string {
        // Get Title
        $parts      = explode("-", $sync->title);
        $titlePrice = trim(end($parts));
        if(strpos($titlePrice, '$') === 0) {
            array_pop($parts);
        }

        // Clean Up Title
        $this->title = trim(implode("-", $parts));
        $this->log->debug("Retrieved title to search for: " . $this->title);
        return $this->title;
    }

    /**
     * Clean Up Category
     * 
     * @param Sync $sync
     * @return string
     */
    private function cleanCat(Sync $sync): string {
        // Fix Ampersand
        $category = str_replace("&amp;", "&", $sync->areacat);

        // Remove Tags
        $this->cat = trim(strip_tags($category));
        $this->log->debug("Retrieved category to search for: " . $this->cat);
        return $this->cat;
    }

    /**
     * Clean Up Subarea
     * 
     * @return string
     */
    private function cleanSubarea(): string {
        // Get View URL
        $this->subarea = '';
        if($this->viewUrl) {
            $this->log->debug("Try getting subarea code out of URL: " . $this->viewUrl);

            // Parse Parts from URL
            $sub = '';
            $url = preg_replace("/https?:\/\//", "", $this->viewUrl);
            $parts = explode("/", $url);
            if(count($parts) >= 4) {
                $sub = $parts[1];
            }

            // Handle Subarea
            if(!empty($sub)) {
                // Get Subarea
                $subarea = app()->make(SubareaRepositoryInterface)->get(['code' => $sub]);

                // Update Subarea
                $this->log->debug('Retrieved subarea: "' . $subarea->name .
                                    '" and "' . $subarea->alt_name .
                                    '" with code: "' . $sub . '"');
                $this->subarea = $subarea->name;
            }
        }

        // Return None
        return $this->subarea;
    }

    /**
     * Clean Up Price
     * 
     * @param Sync $sync
     * @return string
     */
    private function cleanPrice(Sync $sync): string {
        // Fix Price From Sync
        $price = 0;
        if($sync->price) {
            $price = preg_replace("/[^0-9.]/", '', $sync->price);
        }

        // Return Price
        $this->price = number_format($price, 2);
        $this->log->debug("Cleaned up price to search for: " . $this->price);
        return $this->price;
    }

    /**
     * Clean Up Dates
     * 
     * @param Sync $sync
     * @return string
     */
    private function cleanDates(Sync $sync): string {
        // Parse Posted Date
        $posted = $this->parsePostedDate($sync->postedAt, $this->timezone);

        // No Posted Date? Set a New One!
        if(empty($posted) || $posted === '0000-00-00 00:00:00' || $posted === '1969-12-31 19:00:00') {
            $posted = date("Y-m-d H:i:s");
        }
        $this->posted = $this->added = $posted;

        // Check Drafted Date
        $drafted = $this->drafted;
        if(empty($drafted) || $this->drafted === '0000-00-00 00:00:00' || $this->drafted === '1969-12-31 19:00:00') {
            $this->drafted = $this->posted;
        }

        // Return Posted Date
        return $this->posted;
    }

    /**
     * Parse Posted Date From CL
     *
     * @param string $date
     * @param string $zone
     * @return string
     */
    public function parsePostedDate(string $date, string $zone = ''): string {
        // Parse Date
        $postedDate = trim(strip_tags($date));
        if(!empty($postedDate)) {
            $this->log->debug("Get posted date in UTC: " . $postedDate);
            $dateTime = str_replace("Z", "", str_replace("T", " ", $postedDate));

            // Get Check Behind Timezone
            $zone1 = explode("-", $dateTime);
            $behind = end($zone1);
            if($behind == $dateTime) {
                $behind = 0;
            }
            $this->log->debug("Hours behind: " . $behind . " for posted date");
            if (!empty($behind) && strlen($behind) == 4) {
                $timezone = intval($behind) / 100;
                $datetime = str_replace("-" . $behind, "", $datetime);
            }

            // Check Ahead Timezone
            $zone2 = explode("+", $datetime);
            $ahead = end($zone2);
            if($ahead == $datetime) {
                $ahead = 0;
            }
            $this->log->debug("Hours ahead: " . $ahead . " for posted date");
            if (!empty($ahead) && strlen($ahead) == 4) {
                $timezone = intval($ahead) / -100;
                $datetime = str_replace("+" . $ahead, "", $datetime);
            }

            // No Built-in Timezone?
            if(empty($timezone) && !empty($zone)) {
                // Get Zone
                $exp = explode(":", $zone);

                // Valid Integer Offset?
                if(!empty($exp[0])) {
                    // Behind?
                    if(strpos('-', $exp[0]) !== FALSE) {
                        $behind = preg_replace('/^-0?/', '', $exp[0]);
                        if(!empty($behind) && is_numeric($ahead)) {
                            $timezone = intval($behind);
                        }
                    }

                    // Ahead?
                    else {
                        $ahead = preg_replace('/^\+?0?/', '', $exp[0]);
                        if(!empty($ahead) && is_numeric($ahead)) {
                            $timezone = -1 * intval($ahead);
                        }
                    }
                }
            }

            // Set Timestamp
            $this->log->debug("Get timezone offset for posted date: " . $timezone);
            $date = strtotime($datetime);
            $timestamp = $date + ($timezone * 60 * 60);

            // Set Posted Date
            $postedDate = date("Y-m-d H:i:s", $timestamp);
            $this->log->debug("Get posted date from timestamp: " . $postedDate);
        }

        return $postedDate;
    }
}