<?php

namespace App\Services\Integration\Facebook;

use App\Exceptions\Integration\Facebook\FailedCreateProductFeedException;
use App\Exceptions\Integration\Facebook\FailedValidateAccessTokenException;
use App\Exceptions\Integration\Facebook\MissingFacebookAccessTokenException;
use App\Exceptions\Integration\Facebook\ExpiredFacebookAccessTokenException;
use App\Exceptions\Integration\Facebook\FailedReceivingLongLivedTokenException;
use FacebookAds\Api;
use FacebookAds\Http\Client;
use FacebookAds\Http\Request;
use FacebookAds\Http\Parameters;
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
    const GRAPH_API_VERSION = '8.0';

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
        $this->request->setGraphVersion(self::GRAPH_API_VERSION);
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
            'refresh_token' => NULL,
            'is_valid' => $this->validateAccessToken($accessToken->access_token),
            'is_expired' => true
        ];

        // Access Token is Valid?
        if($result['is_valid']) {
            $result['is_expired'] = $this->isAccessTokenExpired($accessToken->access_token);

            // Get Long-Lived Access Token
            if(empty($accessToken->refresh_token)) {
                $result['refresh_token'] = $this->getLongLivedAccessToken($accessToken->access_token);
            }
        }

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
        if(empty($accessToken->access_token)) {
            throw new MissingFacebookAccessTokenException;
        }

        // Try to Get SDK!
        try {
            // Return SDK
            $this->api = Api::init($_ENV['FB_SDK_APP_ID'], $_ENV['FB_SDK_APP_SECRET'], $accessToken->access_token);
        } catch(\Exception $e) {
            $this->api = null;
            Log::error("Exception returned initializing facebook api: " . $ex->getMessage() . ': ' . $ex->getTraceAsString());
        }

        // Return SDK
        return $this->api;
    }

    /**
     * Validate Access Token
     * 
     * @param string $accessToken
     * @return boolean
     */
    private function validateAccessToken($accessToken) {
        // Set Access Token
        $params = new Parameters();
        $params->enhance([
            'access_token' => $accessToken,
            'input_token' => $accessToken
        ]);
        $this->request->setQueryParams($params);

        // Set Path to Validate Access Token
        $this->request->setPath('/debug_token');

        // Catch Error
        try {
            // Get URL
            $response = $this->client->sendRequest($this->request);
            var_dump($response);

            // Return Response;
            return $response;
        } catch (\Exception $ex) {
            // Expired Exception?
            $msg = $ex->getMessage();
            Log::error("Exception returned during schedule feed: " . $ex->getMessage() . ': ' . $ex->getTraceAsString());
            if(strpos($msg, 'Session has expired')) {
                throw new ExpiredFacebookAccessTokenException;
            } else {
                throw new FailedValidateAccessTokenException;
            }
        }

        // Return Null
        return null;
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
