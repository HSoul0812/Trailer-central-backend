<?php

namespace App\Services\Integration\Facebook;

use App\Exceptions\Integration\Facebook\FailedGetProductFeedException;
use App\Exceptions\Integration\Facebook\FailedGetConversationsException;
use App\Exceptions\Integration\Facebook\FailedGetMessagesException;
use App\Exceptions\Integration\Facebook\FailedDeleteProductFeedException;
use App\Exceptions\Integration\Facebook\FailedCreateProductFeedException;
use App\Exceptions\Integration\Facebook\MissingFacebookAccessTokenException;
use App\Exceptions\Integration\Facebook\ExpiredFacebookAccessTokenException;
use App\Exceptions\Integration\Facebook\FailedReceivingLongLivedTokenException;
use App\Models\Integration\Auth\AccessToken;
use App\Repositories\Integration\Facebook\PageRepositoryInterface;
use App\Services\CRM\Interactions\Facebook\DTOs\ChatConversation;
use App\Services\CRM\Interactions\Facebook\DTOs\ChatMessage;
use FacebookAds\Api;
use FacebookAds\Http\Request;
use FacebookAds\Http\Parameters;
use FacebookAds\Object\Application;
use FacebookAds\Object\Page;
use FacebookAds\Object\ProductCatalog;
use FacebookAds\Object\UnifiedThread;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

/**
 * Class BusinessService
 * 
 * @package App\Services\Integration\Facebook
 */
class BusinessService implements BusinessServiceInterface
{
    /**
     * @const string
     */
    const APP_TYPE_DEFAULT = 'marketing';

    /**
     * @const string
     */
    const APP_TYPE_CHAT = 'chat';

    /**
     * @const array
     */
    const APP_TYPES = [
        self::APP_TYPE_DEFAULT,
        self::APP_TYPE_CHAT
    ];


    /**
     * @const string
     */
    const GRAPH_API_VERSION = '8.0';

    /**
     * @const int
     */
    const PER_PAGE_LIMIT = 100;


    /**
     * @var string : marketing|chat
     */
    protected $type = 'marketing';


    /**
     * @var PageRepositoryInterface
     */
    protected $pages;


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
     * Log
     */
    private $log;


    /**
     * Construct Http Client/Request
     */
    public function __construct(PageRepositoryInterface $pages, Request $request) {
        // Get Pages Repository
        $this->pages = $pages;

        // Init Request
        $this->request = $request;
        $this->request->setGraphVersion(self::GRAPH_API_VERSION);

        // Initialize Logger
        $this->log = Log::channel('facebook');
    }

    /**
     * Set App Type
     * 
     * @param string $type
     * @return void
     */
    public function setAppType(string $type) {
        // Type is Valid?
        if(in_array($type, self::APP_TYPES)) {
            $this->type = $type;
        } else {
            $this->type = self::APP_TYPE_DEFAULT;
        }
    }


    /**
     * Get Page Token
     * 
     * @param AccessToken $accessToken
     * @param int $pageId
     * @return string
     */
    public function pageToken(AccessToken $accessToken, int $pageId): string {
        // Get API
        $this->initApi($accessToken);

        // Get Page
        $fbPage = new Page($pageId);
        $page = $fbPage->getSelf(['access_token']);

        // Return Payload Results
        return $page->access_token;
    }

    /**
     * Get Refresh Token
     * 
     * @param array $params
     * @return array of validation info
     */
    public function refresh($params) {
        // Initialize Vars
        $refresh = null;

        // Relation Type is FB Page?
        if(!empty($params['relation_type']) && $params['relation_type'] === 'fbapp_page') {
            // Get Long-Lived Access Tokens for All Pages
            $pages = $this->getPages($params['account_id'], $params['refresh_token']);

            // Find Current Page
            foreach($pages as $page) {
                $item = $page->exportAllData();
                if($item['id'] == $params['page_id']) {
                    $refresh = $item['access_token'];
                    break;
                }
            }
        }
        // Get Standard Long-Lived Access Token
        else {
            // Get Long-Lived Access Token for User
            $refresh = $this->getLongLivedAccessToken($params['access_token']);
        }

        // Return Payload Results
        return $refresh;
    }

    /**
     * Validate Facebook SDK Access Token Exists and Refresh if Possible
     * 
     * @param string || AccessToken $accessToken
     * @param array scopes to use to validate if no scopes exist on access token
     * @return array of validation info
     */
    public function validate($accessToken, $scopes = []) {
        // Configure Client
        $this->initApi($accessToken);

        // Initialize Vars
        $result = $this->validateAccessToken($accessToken, $scopes);
        $result['refresh_token'] = null;

        // Access Token is Valid?
        if($result['is_valid']) {
            // Get Long-Lived Access Token
            if(empty($accessToken->refresh_token)) {
                // Get Page Long Lived Token
                if(is_string($accessToken)) {
                    $result['refresh_token'] = $this->getLongLivedAccessToken($accessToken);
                } else {
                    $result['refresh_token'] = $this->getLongLivedAccessToken($accessToken->access_token);
                }
            }
        }

        // Return Payload Results
        return $result;
    }


    /**
     * Validate a Feed Exists
     * 
     * @param AccessToken $accessToken
     * @param int $catalogId
     * @param int $feedId
     * @return $catalog->createProductFeed || null
     */
    public function validateFeed($accessToken, $catalogId, $feedId) {
        // Configure Client
        $this->initApi($accessToken);

        // Get Product Catalog
        try {
            // Get Catalog
            $catalog = new ProductCatalog($catalogId);

            // Get Feeds
            $feeds = $catalog->getProductFeeds();
            $data = ['id' => null];
            foreach($feeds as $feed) {
                $item = $feed->exportAllData();
                if($item['id'] == $feedId) {
                    $data['id'] = $item['id'];
                    break;
                }
            }

            // Return Data Result
            return $data;
        } catch (\Exception $ex) {
            // Expired Exception?
            $msg = $ex->getMessage();
            $this->log->error("Exception returned during get product feed: " . $ex->getMessage() . ': ' . $ex->getTraceAsString());
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
     * Delete a Feed
     * 
     * @param AccessToken $accessToken
     * @param int $catalogId
     * @param int $feedId
     * @return delete
     */
    public function deleteFeed($accessToken, $catalogId, $feedId) {
        // Configure Client
        $this->initApi($accessToken);

        // Get Product Catalog
        try {
            // Get Catalog
            $catalog = new ProductCatalog($catalogId);

            // Get Feeds
            $feeds = $catalog->getProductFeeds();
            $data = ['id' => null];
            foreach($feeds as $feed) {
                $item = $feed->exportAllData();
                if($item['id'] == $feedId) {
                    $feed->deleteSelf();
                    break;
                }
            }

            // Return Data Result
            return true;
        } catch (\Exception $ex) {
            // Expired Exception?
            $msg = $ex->getMessage();
            $this->log->error("Exception returned during get product feed: " . $ex->getMessage() . ': ' . $ex->getTraceAsString());
            if(strpos($msg, 'Session has expired')) {
                throw new ExpiredFacebookAccessTokenException;
            } else {
                throw new FailedDeleteProductFeedException;
            }
        }

        // Return Null
        return false;
    }

    /**
     * Schedule a Feed
     * 
     * @param AccessToken $accessToken
     * @param int $catalogId
     * @param string $feedUrl
     * @param string $feedName
     * @return $catalog->createProductFeed || null
     */
    public function scheduleFeed($accessToken, $catalogId, $feedUrl, $feedName) {
        // Configure Client
        $this->initApi($accessToken);

        // Get Product Catalog
        try {
            // Get Catalog
            $catalog = new ProductCatalog($catalogId);

            // Create Product Feed
            $data = $catalog->createProductFeed([], [
                'name' => $feedName,
                'schedule' => [
                    'interval' => 'HOURLY',
                    'url' => $feedUrl
                ]
            ])->exportAllData();

            // Return Data Result
            return $data;
        } catch (\Exception $ex) {
            // Expired Exception?
            $msg = $ex->getMessage();
            $this->log->error("Exception returned during schedule feed: " . $ex->getMessage() . ': ' . $ex->getTraceAsString());
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
     * Get Conversations for Page
     * 
     * @param AccessToken $accessToken
     * @param int $pageId
     * @param int $limit default: 0
     * @param string $after default: ''
     * @return Collection<ChatConversation>
     */
    public function getConversations(AccessToken $accessToken, int $pageId, int $limit = 0, string $after = '', Collection $collection = null): Collection {
        // Initialize Collection of Conversations
        if(empty($collection)) {
            $collection = new Collection();
        }

        // Configure Client
        $this->initApi($accessToken);
        $page = $this->pages->getByPageId($pageId);

        // Get Page
        try {
            // Get Conversations
            $fbPage = new Page($pageId);
            $conversations = $fbPage->getConversations(
                ['id', 'link', 'updated_time', 'snippet', 'message_count', 'participants'],
                ['limit' => $limit ?: self::PER_PAGE_LIMIT, 'after' => $after]
            );
            foreach($conversations as $conversation) {
                $collection->push(ChatConversation::getFromUnifiedThread($conversation, $page));
            }

            // Get Next
            $next = $conversations->getNext();
            if(!empty($next)) {
                $this->log->debug("Retrieved " . $collection->count() . " conversations so far, getting next " . $limit . " conversations");
                return $this->getConversations($accessToken, $pageId, $limit, $conversations->getAfter(), $collection);
            }

            // Return Collection<ChatConversation>
            $this->log->debug("Returned " . $collection->count() . " conversations from the page #" . $pageId);
            return $collection;
        } catch (\Exception $ex) {
            // Expired Exception?
            $msg = $ex->getMessage();
            $this->log->error("Exception returned during get conversations: " . $ex->getMessage() . ': ' . $ex->getTraceAsString());
            if(strpos($msg, 'Session has expired')) {
                throw new ExpiredFacebookAccessTokenException;
            } else {
                throw new FailedGetConversationsException;
            }
        }

        // Return Empty Collection
        return $collection;
    }

    /**
     * Get Conversations for Page
     * 
     * @param AccessToken $accessToken
     * @param string $conversationId
     * @param int $limit default: 0
     * @param string $after default: ''
     * @return Collection<ChatMessage>
     */
    public function getMessages(AccessToken $accessToken, string $conversationId, int $limit = 0, string $after = '', Collection $collection = null): Collection {
        // Initialize Collection of Conversations
        if(empty($collection)) {
            $collection = new Collection();
        }

        // Configure Client
        $this->initApi($accessToken);

        // Get Page
        try {
            $conversation = new UnifiedThread();
            $conversation->setId($conversationId);

            // Get Conversations
            $messages = $conversation->getMessages(
                ['id', 'created_time', 'message', 'from', 'to', 'tags'],
                ['limit' => $limit ?: self::PER_PAGE_LIMIT, 'after' => $after]
            );
            foreach($messages as $message) {
                $collection->push(ChatMessage::getFromCrud($message, $conversationId));
            }

            // Get Next
            $next = $messages->getNext();
            if(!empty($next)) {
                $this->log->debug("Retrieved " . $collection->count() . " messages so far, getting next " . $limit . " messages");
                return $this->getMessages($accessToken, $conversationId, $limit, $messages->getAfter(), $collection);
            }

            // Return Collection<ChatMessage>
            $this->log->debug("Returned " . $collection->count() . " messages from the conversation #" . $conversationId);
            return $collection;
        } catch (\Exception $ex) {
            // Expired Exception?
            $msg = $ex->getMessage();
            $this->log->error("Exception returned during get messages: " . $ex->getMessage() . ': ' . $ex->getTraceAsString());
            if(strpos($msg, 'Session has expired')) {
                throw new ExpiredFacebookAccessTokenException;
            } else {
                throw new FailedGetMessagesException;
            }
        }

        // Return Empty Collection
        return $collection;
    }


    /**
     * Initialize API
     * 
     * @param string || AccessToken $accessToken
     * @return API
     */
    private function initApi($accessToken) {
        // Access Token Missing?
        if(!is_string($accessToken) && empty($accessToken->refresh_token) && empty($accessToken->access_token)) {
            throw new MissingFacebookAccessTokenException;
        }

        // Try to Get SDK!
        try {
            // Get Final Token
            $apiToken = $accessToken; // Assuming this is a Page Token
            if(!empty($accessToken->refresh_token)) {
                $apiToken = $accessToken->refresh_token;
            }
            elseif(!empty($accessToken->access_token)) {
                $apiToken = $accessToken->access_token;
            }

            // Return SDK
            $this->api = Api::init(
                $this->getAppId(),
                $this->getAppSecret(),
                $apiToken
            );
        } catch(\Exception $e) {
            $this->api = null;
            $this->log->error("Exception returned initializing facebook api: " . $ex->getMessage() . ': ' . $ex->getTraceAsString());
        }

        // Return SDK
        return $this->api;
    }

    /**
     * Validate Access Token
     * 
     * @param string || AccessToken $accessToken
     * @return boolean
     */
    private function validateAccessToken($accessToken, $scopes = []) {
        // Get Final Token
        $inputToken = $accessToken; // Assuming this is a Page Token
        if(!empty($accessToken->refresh_token)) {
            $inputToken = $accessToken->refresh_token;
        }
        elseif(!empty($accessToken->access_token)) {
            $inputToken = $accessToken->access_token;
        }

        // Scopes Don't Exist on Access Token?
        if(!empty($accessToken->scope)) {
            $scopes = $accessToken->scope;
        }

        // Set Access Token
        $params = new Parameters();
        $params->enhance([
            'access_token' => $this->getAppId() . '|' . $this->getAppSecret(),
            'input_token' => $inputToken
        ]);
        $this->request->setQueryParams($params);

        // Set Path to Validate Access Token
        $this->request->setPath('/debug_token');

        // Catch Error
        try {
            // Get URL
            $response = $this->request->getClient()->sendRequest($this->request);

            // Validate!
            $content = $response->getContent();
            $validate = [
                'is_valid' => $content['data']['is_valid'],
                'is_expired' => (time() > ($content['data']['expires_at'] - 30))
            ];

            // Check Valid Scopes!
            foreach($content['data']['scopes'] as $scope) {
                if(!in_array($scope, $scopes)) {
                    $validate['is_valid'] = false;
                }
            }

            // Return Response;
            return $validate;
        } catch (\Exception $ex) {
            // Expired Exception?
            $this->log->error("Exception returned trying to validate access token: " . $ex->getMessage() . ': ' . $ex->getTraceAsString());
        }

        // Return Defaults
        return [
            'is_valid' => false,
            'is_expired' => true
        ];
    }

    /**
     * Get Pages
     * 
     * @param string || AccessToken $accessToken
     * @return boolean
     */
    private function getPages($accountId, $accessToken) {
        // Configure Client
        $this->initApi($accessToken);

        // Get Product Catalog
        try {
            // Get Application
            $app = new Application($accountId);

            // Get All Pages
            return $app->getAccounts();
        } catch (\Exception $ex) {
            // Expired Exception?
            $msg = $ex->getMessage();
            $this->log->error("Exception returned getting accounts: " . $ex->getMessage() . ': ' . $ex->getTraceAsString());
            if(strpos($msg, 'Session has expired')) {
                throw new ExpiredFacebookAccessTokenException;
            } else {
                throw new FailedGetFacebookAccountsException;
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
    private function getLongLivedAccessToken($accessToken) {
        // Set Access Token
        $params = new Parameters();
        $params->enhance([
            'access_token' => $this->getAppId() . '|' . $this->getAppSecret(),
            'grant_type' => 'fb_exchange_token',
            'client_id' => $this->getAppId(),
            'client_secret' => $this->getAppSecret(),
            'fb_exchange_token' => $accessToken
        ]);
        $this->request->setQueryParams($params);

        // Set Path to Validate Access Token
        $this->request->setPath('/oauth/access_token');

        // Catch Error
        try {
            // Get URL
            $response = $this->request->getClient()->sendRequest($this->request);

            // Return Access Token
            return $response->getContent();
        } catch (\Exception $ex) {
            // Expired Exception?
            $msg = $ex->getMessage();
            $this->log->error("Exception returned trying to get long-lived access token: " . $ex->getMessage() . ': ' . $ex->getTraceAsString());
            if(strpos($msg, 'Session has expired')) {
                throw new ExpiredFacebookAccessTokenException;
            } else {
                throw new FailedReceivingLongLivedTokenException;
            }
        }

        // Return Null
        return null;
    }


    /**
     * Get App ID For Provided Type
     */
    private function getAppId() {
        return config('oauth.fb.' . $this->type . '.app.id');
    }

    /**
     * Get App Secret For Provided Type
     */
    private function getAppSecret() {
        return config('oauth.fb.' . $this->type . '.app.secret');
    }

    /**
     * Get App Scopes For Provided Type
     */
    private function getAppScopes() {
        return config('oauth.fb.' . $this->type . '.scopes');
    }
}
