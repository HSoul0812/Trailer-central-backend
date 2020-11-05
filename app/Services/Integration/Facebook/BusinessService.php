<?php

namespace App\Services\Integration\Facebook;

use FacebookAds\Api;
use FacebookAds\Logger\CurlLogger;
use FacebookAds\Object\AdAccount;
use FacebookAds\Object\Campaign;
use FacebookAds\Object\Fields\CampaignFields;
use FacebookAds\Object\ProductCatalog;
use FacebookAds\Object\ProductFeed;
use FacebookAds\Logger\CurlLogger;
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
    protected $sdk;


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
            'is_valid' => !empty($this->sdk) ? true : false,
            'is_expired' => !empty($this->sdk) ? false : true
        ];

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
        } catch (Exception $ex) {
            echo $ex->getMessage() . PHP_EOL . PHP_EOL;
            echo $ex->getTraceAsString();
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
            throw new MissingFacebookIdTokenException;
        }

        // Try to Get SDK!
        try {
            // Return SDK
            $this->sdk = FacebookAds\Api::init($_ENV['FB_SDK_APP_ID'], $_ENV['FB_SDK_APP_SECRET'], $accessToken->access_token);
        } catch(\Exception $e) {
            $this->sdk = null;
        }

        // Return SDK
        return $sdk;
    }

    /**
     * Validate ID Token
     * 
     * @param AccessToken $accessToken
     * @return boolean
     */
    private function validateIdToken($accessToken) {
        // Invalid
        $validate = false;

        // Validate ID Token
        try {
            // Verify ID Token is Valid
            $payload = $this->client->verifyIdToken($accessToken->id_token);
            if ($payload) {
                $validate = true;
            }
        }
        catch (\Exception $e) {
            // We actually just want to verify this is true or false
            // If it throws an exception, that means its false, the token isn't valid
            Log::error('Exception returned for Google Access Token:' . $e->getMessage() . ': ' . $e->getTraceAsString());
        }

        // Return Validate
        return $validate;
    }

    /**
     * Refresh Access Token
     * 
     * @return array of expired status, also return new token if available
     */
    private function refreshAccessToken() {
        // Set Expired
        $result = [
            'expired' => true
        ];

        // Validate If Expired
        try {
            // If there is no previous token or it's expired.
            $this->client->isAccessTokenExpired();
            if ($this->client->isAccessTokenExpired()) {
                // Refresh the token if possible, else fetch a new one.
                if ($refreshToken = $this->client->getRefreshToken()) {
                    if($newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken)) {
                        $result['access_token'] = $newToken;
                        $result['expired'] = false;
                    }
                }
            }
            // Its Not Expired!
            else {
                $result['expired'] = false;
            }
        } catch (\Exception $e) {
            // We actually just want to verify this is true or false
            // If it throws an exception, that means its false, the token isn't valid
            Log::error('Exception returned for Google Refresh Access Token:' . $e->getMessage() . ': ' . $e->getTraceAsString());
        }

        // Return Result
        return $result;
    }
}
