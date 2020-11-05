<?php

namespace App\Services\Integration\Facebook;

use App\Exceptions\Integration\Facebook\FailedCreateProductFeedException;
use App\Exceptions\Integration\Facebook\MissingFacebookAccessTokenException;
use App\Exceptions\Integration\Facebook\ExpiredFacebookAccessTokenException;
use FacebookAds\Api;
use FacebookAds\Http\Client;
use FacebookAds\Http\Request;
use FacebookAds\Logger\CurlLogger;
use FacebookAds\Object\AdAccount;
use FacebookAds\Object\Campaign;
use FacebookAds\Object\Fields\CampaignFields;
use FacebookAds\Object\ProductCatalog;
use FacebookAds\Object\ProductFeed;
use Illuminate\Support\Facades\Log;

/**
 * Class BusinessService
 * 
 * @package App\Services\Integration\Facebook
 */
class BusinessService implements BusinessServiceInterface
{
    /**
     * @var FacebookAds\Api
     */
    protected $api;

    /**
     * @var FacebookAds\Http\Client
     */
    protected $client;

    /**
     * @var FacebookAds\Http\Request
     */
    protected $request;


    /**
     * Construct Http Client/Request
     */
    public function __construct() {
        // Init Request
        $this->client = new Client();
        $this->request = new Request($this->client);
    }


    /**
     * Validate Facebook SDK Access Token Exists and Refresh if Possible
     * 
     * @param AccessToken $accessToken
     * @return array of validation info
     */
    public function validate($accessToken) {
        // Configure Client
        $this->initApi($accessToken);

        // Initialize Vars
        $result = [
            'access_token' => $accessToken->access_token,
            'is_valid' => $this->validateAccessToken($accessToken->access_token),
            'is_expired' => true
        ];

        // Get Refresh Token
        $result['is_expired'] = $this->isAccessTokenExpired($accessToken->access_token);

        // Get Long-Lived Access Token
        $result['access_token'] = $this->getLongLivedAccessToken($accessToken->access_token);

        // Return Payload Results
        return $result;
    }

    /**
     * Schedule a Feed
     */
    public function scheduleFeed($accessToken, $filename) {
        // Configure Client
        $this->initApi($accessToken);

        // Get Product Catalog
        try {
            // Get Catalog
            $catalog = new ProductCatalog($_ENV['FB_SDK_CATALOG_ID']);

            // Create Product Feed
            $data = $catalog->createProductFeed(
                array(),
                array(
                    'name' => 'Test Feed',
                    'schedule' => array(
                        'interval' => 'DAILY',
                        'url' => $filename,
                        'hour' => '22'
                    )
                )
            )->exportAllData();

            // Return Data Result
            return $data;
        } catch (\Exception $ex) {
            // Expired Exception?
            $msg = $ex->getMessage();
            Log::error("Exception returned during schedule feed: " . $ex->getMessage() . ': ' . $ex->getTraceAsString());
            if(strpos($msg, 'Session has expired')) {
                throw new ExpiredFacebookAccessTokenException;
            } else {
                throw new FailedCreateProductFeedException;
            }
        }

        // Return Null
        return null;
    }


    /**
     * Initialize API
     * 
     * @param AccessToken $accessToken
     * @return API
     */
    private function initApi($accessToken) {
        // ID Token Missing?
        if(empty($accessToken->id_token)) {
            throw new MissingFacebookAccessTokenException;
        }

        // Try to Get SDK!
        try {
            // Return SDK
            $this->api = Api::init($_ENV['FB_SDK_APP_ID'], $_ENV['FB_SDK_APP_SECRET'], $accessToken->access_token);
        } catch(\Exception $e) {
            $this->api = null;
        }

        // Return SDK
        return $this->api;
    }

    /**
     * Validate Access Token
     * 
     * @param AccessToken $accessToken
     * @return boolean
     */
    private function validateAccessToken($accessToken) {
        // Set Path to Validate Access Token
        $this->request->setPath('/debug_token?input_token=' + $accessToken);

        // Get URL
        $response = $this->request->getUrl();
        var_dump($response);
    }

    /**
     * Refresh Access Token
     * 
     * @return array of expired status, also return new token if available
     */
    private function refreshAccessToken($accessToken) {
        // Set Expired
        $result = [
            'expired' => true
        ];

        // Return Result
        return $result;
    }
}
