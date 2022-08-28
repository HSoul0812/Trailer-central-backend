<?php

namespace App\Services\Integration\Facebook;

use App\Models\Integration\Auth\AccessToken;
use App\Models\Integration\Facebook\Chat;
use App\Jobs\CRM\Interactions\Facebook\MessageJob;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Repositories\Integration\Facebook\ChatRepositoryInterface;
use App\Repositories\Integration\Facebook\PageRepositoryInterface;
use App\Services\Integration\Facebook\BusinessService;
use App\Services\Integration\Facebook\BusinessServiceInterface;
use App\Transformers\Integration\Facebook\ChatTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\DispatchesJobs;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

/**
 * Class ChatService
 * 
 * @package App\Services\Integration\Facebook
 */
class ChatService implements ChatServiceInterface
{
    use DispatchesJobs;

    /**
     * @var ChatRepositoryInterface
     */
    protected $chat;

    /**
     * @var PageRepositoryInterface
     */
    protected $pages;

    /**
     * @var TokenRepositoryInterface
     */
    protected $tokens;

    /**
     * @var BusinessServiceInterface
     */
    protected $sdk;

    /**
     * @var ChatTransformer
     */
    protected $transformer;

    /**
     * @var Manager
     */
    private $fractal;

    /**
     * Log
     */
    private $log;

    /**
     * Construct Facebook Service
     */
    public function __construct(
        ChatRepositoryInterface $chat,
        PageRepositoryInterface $pages,
        TokenRepositoryInterface $tokens,
        BusinessServiceInterface $sdk,
        ChatTransformer $transformer,
        Manager $fractal
    ) {
        $this->chat = $chat;
        $this->pages = $pages;
        $this->tokens = $tokens;
        $this->sdk = $sdk;
        $this->sdk->setAppType(BusinessService::APP_TYPE_CHAT);
        $this->transformer = $transformer;
        $this->fractal = $fractal;

        $this->fractal->setSerializer(new NoDataArraySerializer());

        // Initialize Logger
        $this->log = Log::channel('facebook');
    }

    /**
     * Show Chat Response
     * 
     * @param array $params
     * @return array<ChatTransformer>
     */
    public function show(array $params): array {
        // Get Access Token
        $chat = $this->chat->get($params);

        // Return Response
        return $this->response($chat, $chat->accessToken);
    }

    /**
     * Create Chat
     * 
     * @param array $params
     * @return array<ChatTransformer>
     */
    public function create(array $params): array {
        // Create Facebook Page
        $page = $this->pages->create($params);

        // Create Token
        $params['fbapp_page_id'] = $page->id;
        $chat = $this->chat->create($params);

        // Adjust Request
        $params['token_type'] = 'facebook';
        $params['relation_type'] = 'fbapp_chat';
        $params['relation_id'] = $chat->id;

        // Find Refresh Token
        $refresh = $this->sdk->refresh($params);
        if(!empty($refresh)) {
            $params['refresh_token'] = $refresh['access_token'];
            if(isset($refresh['expires_in'])) {
                $params['expires_in'] = $refresh['expires_in'];
                $params['expires_at'] = gmdate("Y-m-d H:i:s", (time() + $refresh['expires_in']));
            }
        }

        // Get Access Token
        $accessToken = $this->tokens->create($params);

        // Get Page Token
        $pageToken = $this->sdk->pageToken($accessToken, $page->page_id);

        // Page Token Exists?
        if(!empty($pageToken)) {
            // Get Access Token
            $pageAccessToken = $this->tokens->create([
                'token_type' => 'facebook',
                'relation_type' => 'fbapp_page',
                'relation_id' => $page->id,
                'access_token' => $pageToken,
                'refresh_token' => $pageToken
            ]);

            // Dispatch Send Message Job
            // $job = new MessageJob($pageAccessToken, $page->page_id);
            // $this->dispatch($job->onQueue('fb-messenger'));
        }

        // Return Response
        return $this->response($chat, $accessToken);
    }

    /**
     * Update Chat
     * 
     * @param array $params
     * @return array<ChatTransformer>
     */
    public function update(array $params): array {
        // Update Facebook Page
        $page = $this->pages->create($params);

        // Create Access Token
        $chat = $this->chat->update($params);

        // Adjust Request
        $params['token_type'] = 'facebook';
        $params['relation_type'] = 'fbapp_chat';
        $params['relation_id'] = $params['id'];
        unset($params['id']);

        // Access Token is Set?
        if(isset($params['access_token']) && empty($params['refresh_token'])) {
            // Find Refresh Token
            $refresh = $this->sdk->refresh($params);
            if(!empty($refresh)) {
                $params['refresh_token'] = $refresh['access_token'];
                if(isset($refresh['expires_in'])) {
                    $params['expires_in'] = $refresh['expires_in'];
                    $params['expires_at'] = gmdate("Y-m-d H:i:s", (time() + $refresh['expires_in']));
                }
            }

            // Create Access Token
            $accessToken = $this->tokens->create($params);
        } else {
            // Get Access Token
            $accessToken = $this->tokens->getRelation($params);
        }

        // Page Token Exists?
        if(isset($params['page_token']) && empty($params['page_refresh_token'])) {
            // Adjust Request
            $params['token_type'] = 'facebook';
            $params['relation_type'] = 'fbapp_page';
            $params['relation_id'] = $page->id;

            // Get Refresh Token
            $refresh = $this->sdk->refresh($params);
            if(!empty($refresh)) {
                $params['refresh_token'] = $refresh;
            } else {
                unset($params['refresh_token']);
            }

            // Get Access Token
            $this->tokens->update($params);
        }

        // Return Response
        return $this->response($chat, $accessToken);
    }

    /**
     * Delete Chat
     * 
     * @param int $id
     * @return boolean
     */
    public function delete(int $id): bool {
        // Delete Access Token
        $this->tokens->delete([
            'token_type' => 'facebook',
            'relation_type' => 'fbapp_chat',
            'relation_id' => $id
        ]);

        // Delete Chat
        return $this->chat->delete($id);
    }

    /**
     * Assign Sales People to Chat
     * 
     * @param array $params
     * @return array<ChatTransformer>
     */
    public function assignSalespeople(array $params): array {

        $this->chat->assignSalespeople($params['id'], $params['sales_person_ids']);

        return $this->show($params);
    }

    /**
     * Return Response
     * 
     * @param Chat $chat
     * @param AccessToken $accessToken
     * @param array $response
     * @return array<ChatTransformer>
     */
    public function response(Chat $chat, AccessToken $accessToken): array {
        // Convert Chat to Array
        $data = new Item($chat, $this->transformer, 'data');
        $response = $this->fractal->createData($data)->toArray();

        // Set Validate
        $response['validate'] = $this->sdk->validate($accessToken);

        // Return Response
        return $response;
    }
}
