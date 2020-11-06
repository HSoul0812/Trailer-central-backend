<?php

namespace App\Services\Integration\Facebook;

use App\Exceptions\Integration\Facebook\FailedGetProductFeedException;
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
        $result = $this->validateAccessToken($accessToken);
        $result['refresh_token'] = null;

        // Access Token is Valid?
        if($result['is_valid']) {
            // Get Long-Lived Access Token
            if(empty($accessToken->refresh_token)) {
                $result['refresh_token'] = $this->getLongLivedAccessToken($accessToken->access_token);
            }
        }

        // Return Payload Results
        return $result;
    }

    /**
     * Validate a Feed Exists
     * 
     * @param AccessToken $accessToken
     * @param int $feedId
     * @return $catalog->createProductFeed || null
     */
    public function validateFeed($accessToken, $feedId) {
        // Configure Client
        $this->initApi($accessToken);

        // Get Product Catalog
        try {
            // Get Feed
            $feed = new ProductFeed($feedId);

            // Create Product Feed
            $data = $feed->getSelf();
            var_dump($data);
            die;

            // Return Data Result
            return $data;
        } catch (\Exception $ex) {
            // Expired Exception?
            $msg = $ex->getMessage();
            Log::error("Exception returned during get product feed: " . $ex->getMessage() . ': ' . $ex->getTraceAsString());
            if(strpos($msg, 'Session has expired')) {
                throw new ExpiredFacebookAccessTokenException;
            } else {
                throw new FailedGetProductFeedException;
            }
        }

        // Return Null
        return null;
    }

    /**
     * Schedule a Feed
     * 
     * @param AccessToken $accessToken
     * @param string $feedUrl
     * @param string $feedName
     * @return $catalog->createProductFeed || null
     */
    public function scheduleFeed($accessToken, $feedUrl, $feedName) {
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
                    'name' => $feedName,
                    'schedule' => array(
                        'interval' => 'DAILY',
                        'url' => $feedUrl,
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
        // Access Token Missing?
        if(empty($accessToken->refresh_token) && empty($accessToken->access_token)) {
            throw new MissingFacebookAccessTokenException;
        }

        // Try to Get SDK!
        try {
            // Return SDK
            $this->api = Api::init(
                $_ENV['FB_SDK_APP_ID'],
                $_ENV['FB_SDK_APP_SECRET'],
                !empty($accessToken->refresh_token) ? $accessToken->refresh_token : $accessToken->access_token
            );
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
     * @param AccessToken $accessToken
     * @return boolean
     */
    private function validateAccessToken($accessToken) {
        // Set Access Token
        $params = new Parameters();
        $params->enhance([
            'access_token' => !empty($accessToken->refresh_token) ? $accessToken->refresh_token : $accessToken->access_token,
            'input_token' => !empty($accessToken->refresh_token) ? $accessToken->refresh_token : $accessToken->access_token
        ]);
        $this->request->setQueryParams($params);

        // Set Path to Validate Access Token
        $this->request->setPath('/debug_token');

        // Catch Error
        try {
            // Get URL
            $response = $this->client->sendRequest($this->request);

            // Validate!
            $content = $response->getContent();
            $validate = [
                'is_valid' => $content['data']['is_valid'],
                'is_expired' => (time() > ($content['data']['expires_at'] - 30))
            ];

            // Check Valid Scopes!
            foreach($content['data']['scopes'] as $scope) {
                if(!in_array($scope, $accessToken->scope)) {
                    $validate['is_valid'] = false;
                }
            }

            // Return Response;
            return $validate;
        } catch (\Exception $ex) {
            // Expired Exception?
            Log::error("Exception returned trying to validate access token: " . $ex->getMessage() . ': ' . $ex->getTraceAsString());
        }

        // Return Defaults
        return [
            'is_valid' => false,
            'is_expired' => true
        ];
    }

    /**
     * Refresh Access Token
     * 
     * @return array of expired status, also return new token if available
     */
    private function getLongLivedAccessToken($accessToken) {
        // Set Access Token
        $params = new Parameters();
        $params->enhance([
            'access_token' => $accessToken,
            'grant_type' => 'fb_exchange_token',
            'client_id' => $_ENV['FB_SDK_APP_ID'],
            'client_secret' => $_ENV['FB_SDK_APP_SECRET'],
            'fb_exchange_token' => $accessToken
        ]);
        $this->request->setQueryParams($params);

        // Set Path to Validate Access Token
        $this->request->setPath('/oauth/access_token');

        // Catch Error
        try {
            // Get URL
            $response = $this->client->sendRequest($this->request);

            // Return Access Token
            return $response->getContent();
        } catch (\Exception $ex) {
            // Expired Exception?
            $msg = $ex->getMessage();
            Log::error("Exception returned trying to get long-lived access token: " . $ex->getMessage() . ': ' . $ex->getTraceAsString());
            if(strpos($msg, 'Session has expired')) {
                throw new ExpiredFacebookAccessTokenException;
            } else {
                throw new FailedReceivingLongLivedTokenException;
            }
        }

        // Return Null
        return null;
    }
}
