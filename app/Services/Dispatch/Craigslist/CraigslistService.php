<?php

namespace App\Services\Dispatch\Craigslist;

use App\Models\Marketing\Craigslist\Queue;
use App\Models\User\AuthToken;
use App\Models\User\Integration\Integration;
use App\Repositories\Marketing\TunnelRepositoryInterface;
use App\Repositories\Marketing\VirtualCardRepositoryInterface;
use App\Repositories\Marketing\Craigslist\AccountRepositoryInterface;
use App\Repositories\Marketing\Craigslist\ActivePostRepositoryInterface;
use App\Repositories\Marketing\Craigslist\DealerRepositoryInterface;
use App\Repositories\Marketing\Craigslist\DraftRepositoryInterface;
use App\Repositories\Marketing\Craigslist\PostRepositoryInterface;
use App\Repositories\Marketing\Craigslist\ProfileRepositoryInterface;
use App\Repositories\Marketing\Craigslist\SchedulerRepositoryInterface;
use App\Repositories\Marketing\Craigslist\SessionRepositoryInterface;
use App\Repositories\Marketing\Craigslist\TransactionRepositoryInterface;
use App\Repositories\Marketing\Craigslist\QueueRepositoryInterface;
use App\Services\Dispatch\Craigslist\DTOs\ClappPost;
use App\Services\Dispatch\Craigslist\DTOs\ClappError;
use App\Services\Dispatch\Craigslist\DTOs\DealerCraigslist;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Class CraigslistService
 *
 * @package App\Services\Dispatch\Facebook
 */
class CraigslistService implements CraigslistServiceInterface
{
    /**
     * @const Craigslist Integration Name
     */
    const INTEGRATION_NAME = 'dispatch_craigslist';


    /**
     * @var AccountRepositoryInterface
     */
    protected $accounts;

    /**
     * @var ActivePostRepositoryInterface
     */
    protected $activePosts;

    /**
     * @var DealerRepositoryInterface
     */
    protected $dealers;

    /**
     * @var DraftRepositoryInterface
     */
    protected $drafts;

    /**
     * @var PostRepositoryInterface
     */
    protected $posts;

    /**
     * @var ProfileRepositoryInterface
     */
    protected $profiles;

    /**
     * @var QueueRepositoryInterface
     */
    protected $queues;

    /**
     * @var SchedulerRepositoryInterface
     */
    protected $scheduler;

    /**
     * @var SessionRepositoryInterface
     */
    protected $sessions;

    /**
     * @var TunnelRepositoryInterface
     */
    protected $tunnels;

    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactions;

    /**
     * @var VirtualRepositoryInterface
     */
    protected $cards;

    /**
     * Construct Craigslist Dispatch Service
     *
     * @param AccountRepositoryInterface $accounts
     * @param ActivePostRepositoryInterface $activePosts
     * @param DealerRepositoryInterface $dealers
     * @param DraftRepositoryInterface $drafts
     * @param PostRepositoryInterface $posts
     * @param ProfileRepositoryInterface $profiles
     * @param QueueRepositoryInterface $queues
     * @param SchedulerRepositoryInterface $scheduler
     * @param SessionRepositoryInterface $sessions
     * @param TunnelRepositoryInterface $tunnels
     * @param TransactionRepositoryInterface $transactions
     * @param VirtualCardRepositoryInterface $cards
     */
    public function __construct(
        AccountRepositoryInterface $accounts,
        ActivePostRepositoryInterface $activePosts,
        DealerRepositoryInterface $dealers,
        DraftRepositoryInterface $drafts,
        PostRepositoryInterface $posts,
        ProfileRepositoryInterface $profiles,
        QueueRepositoryInterface $queues,
        SchedulerRepositoryInterface $scheduler,
        SessionRepositoryInterface $sessions,
        TunnelRepositoryInterface $tunnels,
        TransactionRepositoryInterface $transactions,
        VirtualCardRepositoryInterface $cards
    ) {
        $this->accounts = $accounts;
        $this->activePosts = $activePosts;
        $this->dealers = $dealers;
        $this->drafts = $drafts;
        $this->posts = $posts;
        $this->profiles = $profiles;
        $this->queues = $queues;
        $this->scheduler = $scheduler;
        $this->sessions = $sessions;
        $this->tunnels = $tunnels;
        $this->transactions = $transactions;
        $this->cards = $cards;

        // Initialize Logger
        $this->log = Log::channel('dispatch-cl');
    }


    /**
     * Login to Craigslist
     *
     * @param string $uuid
     * @param string $ip
     * @param string $version
     * @return string
     */
    public function login(string $uuid, string $ip, string $version): string {
        // Log
        $this->log->info('Login request received from client ' . $uuid .
                            ' bearing the IP address ' . $ip .
                            ' on extension version #' . $version);

        // TO DO: Implement Version Checking
        // TO DO: Implement IP Blockages
        // TO DO: Implement saving details to redis/db

        // Get Integration Name
        $integration = Integration::where('name', self::INTEGRATION_NAME)->first();

        // Get Auth Token
        $token = AuthToken::where(['user_type' => 'integration', 'user_id' => $integration->id])->first();

        // Return Access Token
        return $token->access_token;
    }

    /**
     * Get Craigslist Status
     *
     * @param array $params
     * @param null|float $startTime
     * @return Collection<DealerStatus>
     */
    public function status(array $params, ?float $startTime = null): Collection {
        // Get Integration
        if(empty($startTime)) {
            $startTime = microtime(true);
        }

        // Get All Craigslist Dealers
        $dealers = $this->getDealers($params, $startTime);

        // Log Time
        $nowTime = microtime(true);
        $this->log->info('Debug time after Returning Dealers Collection: ' . ($nowTime - $startTime));

        // Return Dealers Collection
        return $dealers;
    }

    /**
     * Get Dealer Inventory
     *
     * @param int $dealerId
     * @param array $params
     * @param null|float $startTime
     * @return DealerCraigslist
     */
    public function dealer(int $dealerId, array $params, ?float $startTime = null): DealerCraigslist {
        // Get Integration
        if(empty($startTime)) {
            $startTime = microtime(true);
        }

        // Get Craigslist Dealers
        $clapp = $this->dealers->get([
            'dealer_id' => $dealerId
        ]);

        // Get Parameters for DealerCraigslist
        $params['balance'] = $clapp->balance->balance;
        $dealerClapp = [
            'dealer_id'    => $clapp->dealer_id,
            'balance'      => $clapp->balance->balance,
            'slots'        => $clapp->slots,
            'chrome_mode'  => $clapp->chrome_mode,
            'since'        => $clapp->since,
            'next'         => $clapp->next_session,
            'dealer_name'  => $clapp->dealer->name,
            'dealer_email' => $clapp->dealer->email,
            'dealer_type'  => $clapp->dealer->type,
            'dealer_state' => $clapp->dealer->state
        ];

        // Get Inventory if Needed
        foreach(DealerCraigslist::INVENTORY_INCLUDES as $include) {
            if(!empty($params['include']) && in_array($include, $params['include'])) {
                $nowTime = microtime(true);
                if($include === DealerCraigslist::INCLUDE_INVENTORY) {
                    $dealerClapp[$include] = $this->getInventory($params, $startTime);
                    $this->log->info('Debug time after include ' . $include . ': ' . ($nowTime - $startTime));
                } elseif($include === DealerCraigslist::INCLUDE_UPDATES) {
                    $dealerClapp[$include] = $this->getUpdates($params, $startTime);
                    $this->log->info('Debug time after include ' . $include . ': ' . ($nowTime - $startTime));
                }
            }
        }

        // Include Extra Features
        foreach(DealerCraigslist::AVAILABLE_INCLUDES as $include) {
            if(!empty($params['include']) && in_array($include, $params['include'])) {
                $dealerClapp[$include] = $this->$include->getAll(['dealer_id' => $dealerId]);
                $nowTime = microtime(true);
                $this->log->info('Debug time after include ' . $include . ': ' . ($nowTime - $startTime));
            }
        }
        $response = new DealerCraigslist($dealerClapp);

        // Log Time After Returning Results
        $nowTime = microtime(true);
        $this->log->info('Debug time after creating DealerCraigslist: ' . ($nowTime - $startTime));
        return $response;
    }

    /**
     * Create Listings for Facebook in DB
     *
     * @param array $params
     * @return ClappListing
     */
    public function create(array $params): ClappListing {
        // Log
        $this->log->info('Creating Craigslist Inventory #' .
                            $params['craigslist_id'] . ' with the TC' .
                            ' Inventory #' . $params['inventory_id'] .
                            ' for the CL Dealer #' . $params['dealer_id']);

        // Create ClappPost From Queue
        $queue = $this->queues->get(['queue_id' => $params]);

        // Fix Missing Values
        if(empty($params['session_id'])) {
            $params['session_id'] = $queue->session_id;
        }
        if(empty($params['profile_id'])) {
            $params['profile_id'] = $queue->profile_id;
        }
        if(empty($params['inventory_id'])) {
            $params['inventory_id'] = $queue->inventory_id;
        }

        // Get ClappPost
        $clappPost = ClappPost::fill($queue);

        // Get Draft
        $params['added'] = Carbon::now()->toDateTimeString();
        $draft = $this->getDraft($clappPost, $params);
        $params['drafted'] = $draft['drafted'];

        // Return Clapp Listing With Various Related Data
        return new ClappListing([
            'draft' => $draft,
            'post' => $this->getPost($clappPost, $params),
            'activePost' => $this->getActivePost($clappPost, $params),
            'transaction' => $this->updateBalance($clappPost, $params),
            'session' => $this->updateSession($clappPost, $params)
        ]);
    }


    /**
     * Get Dealers Signed Up with Craigslist
     *
     * @param array $params
     * @param null|float $startTime
     * @return Collection<DealerCraigslist>
     */
    private function getDealers(array $params, ?float $startTime = null): Collection {
        // Empty Type?
        if(!isset($params['type'])) {
            $params['type'] = 'now';
        }

        // Get Craigslist Dealers
        $dealerClapp = $this->dealers->getAll([
            'sort' => '-date_scheduled',
            'import_range' => config('marketing.fb.settings.limit.hours', 0),
            'skip_errors' => config('marketing.fb.settings.limit.errors', 1),
            'per_page' => $params['per_page'] ?? null,
            'type' => $params['type']
        ]);

        // Log Time
        $nowTime = microtime(true);
        $this->log->info('Debug time after Returning All CL Dealers: ' . ($nowTime - $startTime));

        // Loop Facebook Integrations
        $dealers = new Collection();
        foreach($dealerClapp as $clapp) {
            // Append DealerCraigslist to Collection
            $dealers->push(new DealerCraigslist([
                'dealer_id'    => $clapp->dealer_id,
                'slots'        => $clapp->slots,
                'chrome_mode'  => $clapp->chrome_mode,
                'since'        => $clapp->since,
                'next'         => $clapp->next_session,
                'type'         => $params['type'],
                'dealer_name'  => $clapp->dealer->name,
                'dealer_email' => $clapp->dealer->email,
                'dealer_type'  => $clapp->dealer->type,
                'dealer_state' => $clapp->dealer->state
            ]));

            // Log Time
            $this->log->info('Debug time after DealerCraigslist #' . $clapp->dealer_id .
                                ': ' . (microtime(true) - $startTime));
        }

        // Return Dealers Collection
        return $dealers;
    }

    /**
     * Get Inventory to Post
     *
     * @param array $params
     * @param null|float $startTime
     * @return Collection<ClappPost> | Collection<ClappUpdate>
     */
    private function getInventory(array $params, ?float $startTime = null): Collection {
        // Get Totals
        if(empty($startTime)) {
            $startTime = microtime(true);
        }
        if(!isset($params['per_page'])) {
            $params['per_page'] = config('marketing.cl.settings.limit.inventories', 10);
        }

        // Get Inventory
        $queues = $this->scheduler->getReady($params);

        // Log Debug on Getting Inventories or Updates
        $nowTime = microtime(true);
        $this->log->info('Debug time after clapp inventories: ' . ($nowTime - $startTime));

        // Loop Through Inventory Items
        $listings = new Collection();
        foreach ($queues as $queue) {
            $nowTime = microtime(true);
            $clappPost = ClappPost::fill($queue);
            $this->queues->update($clappPost->getParams());
            if($this->validatePost($queue, $clappPost)) {
                $listings->push($clappPost);
            }
            $this->log->info('Debug time ClappPost #' . $queue->session_id . ': ' . ($nowTime - $startTime));
        }

        // Return Results After Checking Balance
        return $this->validateBalance($listings, $params['balance']);
    }

    /**
     * Get Inventory to Update
     *
     * @param array $params
     * @param null|float $startTime
     * @return Collection<ClappPost> | Collection<ClappUpdate>
     */
    private function getUpdates(array $params, ?float $startTime = null): Collection {
        // Get Totals
        if(empty($startTime)) {
            $startTime = microtime(true);
        }
        if(!isset($params['per_page'])) {
            $params['per_page'] = config('marketing.cl.settings.limit.updates', 10);
        }

        // Get Inventory
        $queues = $this->scheduler->getUpdates($params);

        // Log Debug on Getting Inventories or Updates
        $nowTime = microtime(true);
        $this->log->info('Debug time after clapp updates: ' . ($nowTime - $startTime));

        // Loop Through Inventory Items
        $listings = new Collection();
        foreach ($queues as $queue) {
            $nowTime = microtime(true);
            $listings->push(ClappUpdate::fill($queue));
            $this->log->info('Debug time ClappUpdate #' . $queue->session_id . ': ' . ($nowTime - $startTime));
        }

        // Return Results
        return $listings;
    }


    /**
     * Check if the Current Post is Valid
     * 
     * @param Queue $queue
     * @param ClappPost $clapp
     * @return bool
     */
    private function validatePost(Queue $queue, ClappPost $clapp): bool {
        return true;
    }

    /**
     * Check if the Dealer Has Enough Balance for All Posts
     * 
     * @param Collection<ClappPost> $posts
     * @param float $balance
     * @return Collection<ClappPost>
     */
    private function validateBalance(Collection $posts, float $balance): Collection {
        // Get Min Balance
        $minBalance = (int) config('marketing.cl.settings.costs.min', 7);

        // Add Costs From All Posts
        $costs = 0;
        $this->log->info('Checking ' . $posts->count() . ' total posts to ' .
                        'confirm if lower than the current balance of ' . $balance);
        $remaining = new Collection();
        foreach($posts as $post) {
            $costs += $post->qData->costs;

            // Check Balance So Far
            if($balance < $costs || $balance < $minBalance) {
                $this->markError($post->queue, 'pending-billing');
            } else {
                $remaining->push($post);
            }
        }

        // Return Remaining Posts
        return $remaining;
    }

    /**
     * Mark Error and Return Status Based on Error
     * 
     * @param Queue $queue
     * @param string $error
     * @param null|array $params
     * @return Session
     */
    private function markError(Queue $queue, string $error, ?array $params = []): Session {
        // Get Error Status
        $err = ClappError::fill($error, $params);

        // Update Session
        $session = $this->sessions->update([
            'session_id' => $queue->session_id,
            'status' => $err->status,
            'state' => $err->state,
            'text_status' => $err->textStatus
        ]);

        // Update Queue
        $this->queues->update([
            'queue_id' => $queue->queue_id,
            'status' => $err->status,
            'state' => $err->state
        ]);

        // Session Was Changed?
        return $session;
    }


    /**
     * Get Draft From ClappPost
     * 
     * @param ClappPost $clappPost
     * @param array $params
     * @return Draft
     */
    private function getDraft(ClappPost $clappPost, array $params): Draft {
        // Create Draft From ClappPost
        return $this->drafts->createOrUpdate([
            'session_id'   => $clappPost->session_id,
            'queue_id'     => $clappPost->queue_id,
            'inventory_id' => $clappPost->inventory_id,
            'profile_id'   => $clappPost->profile_id,
            'username'     => $clappPost->username,
            'response'     => ($params['status'] === 'done' ? 'OK' : ''),
            'drafted'      => $params['added'],
            'title'        => $clappPost->postingTitle,
            'price'        => $clappPost->ask,
            'category'     => $clappPost->category,
            'area'         => $clappPost->market,
            'subarea'      => $clappPost->subarea,
            'preview'      => $clappPost->preview()
        ]);
    }

    /**
     * Get Post From ClappPost
     * 
     * @param ClappPost $clappPost
     * @param array $params
     * @return Post
     */
    private function getPost(ClappPost $clappPost, array $params): Draft {
        // Create Post From ClappPost
        return $this->posts->createOrUpdate([
            'inventory_id' => $clappPost->inventory_id,
            'session_id'   => $clappPost->session_id,
            'queue_id'     => $clappPost->queue_id,
            'username'     => $clappPost->username,
            'response'     => ($params['status'] === 'done' ? 'OK' : ''),
            'drafted'      => $params['drafted'],
            'posted'       => $params['added'],
            'profile_id'   => $clappPost->profile_id,
            'title'        => $clappPost->postingTitle,
            'price'        => $clappPost->ask,
            'area'         => $clappPost->market,
            'subarea'      => $clappPost->subarea,
            'category'     => $clappPost->category,
            'preview'      => $clappPost->preview(),
            'clID'         => $params['craigslist_id'],
            'cl_status'    => $params['status'],
            'manage_url'   => $params['manage_url'],
            'view_url'     => $params['view_url']
        ]);
    }

    /**
     * Get Active Post From ClappPost
     * 
     * @param ClappPost $clappPost
     * @param array $params
     * @return Post
     */
    private function getActivePost(ClappPost $clappPost, array $params): Draft {
        // Create Active Post From ClappPost
        return $this->activePosts->createOrUpdate([
            'inventory_id' => $clappPost->inventory_id,
            'session_id'   => $clappPost->session_id,
            'queue_id'     => $clappPost->queue_id,
            'username'     => $clappPost->username,
            'response'     => ($params['status'] === 'done' ? 'OK' : ''),
            'drafted'      => $params['drafted'],
            'posted'       => $params['added'],
            'profile_id'   => $clappPost->profile_id,
            'title'        => $clappPost->postingTitle,
            'price'        => $clappPost->ask,
            'area'         => $clappPost->market,
            'subarea'      => $clappPost->subarea,
            'category'     => $clappPost->category,
            'preview'      => $clappPost->preview(),
            'clID'         => $params['craigslist_id'],
            'cl_status'    => $params['status'],
            'manage_url'   => $params['manage_url'],
            'view_url'     => $params['view_url']
        ]);
    }

    /**
     * Update Balance From ClappPost
     * 
     * @param ClappPost $clappPost
     * @param array $params
     * @return null|Transaction
     */
    private function updateBalance(ClappPost $clappPost, array $params): ?Transaction {
        // No Costs?
        if($clappPost->costs < 1 || $params['status'] === 'done') {
            return null;
        }

        // Get Current Balance
        $balance = $this->balances->get($params['dealer_id']);
        $newBalance = ($balance->balance - $clappPost->costs);

        // Create Transaction From ClappPost
        $transaction = $this->transactions->create([
            'dealer_id'    => $params['dealer_id'],
            'ip_addr'      => $params['ip_addr'],
            'user_agent'   => $params['user_agent'],
            'session_id'   => $params['session_id'],
            'queue_id'     => $params['queue_id'],
            'inventory_id' => $params['inventory_id'],
            'amount'       => $clappPost->costs,
            'balance'      => $newBalance,
            'type'         => Transaction::TYPE_POST
        ]);

        // Update Balance
        $this->balances->update(['dealer_id' => $params['dealer_id'], 'balance' => $newBalance]);

        // Return Transaction
        return $transaction;
    }

    /**
     * Update Session From ClappPost
     * 
     * @param ClappPost $clappPost
     * @param array $params
     * @return null|Session
     */
    private function updateSession(ClappPost $clappPost, array $params): ?Session {
        // Get Status
        $status = $params['status'];
        if($status === 'error') {
            $error = $params['state'] ?? $params['status'];
            return $this->markError($clappPost->queue, $error, $params);
        }

        // Update Session
        $session = $this->sessions->update([
            'session_id' => $clappPost->queue->session_id,
            'status' => $params['status'],
            'state' => $params['state'] ?? 'completed-ok',
            'text_status' => $params['text_status'] ?? 'One item completed successfully!'
        ]);

        // Update Queue
        $this->queues->update([
            'queue_id' => $clappPost->queue->queue_id,
            'status' => $params['status'],
            'state' => $params['state'] ?? 'completed-ok',
        ]);

        // Session Was Changed?
        return $session;
    }


    /**
     * Save Logs to Dispatch Logs
     *
     * @param Craigslist $integration
     * @param string $type missing|updates|sold
     * @param array $params
     * @return Pagination<InventoryFacebook>
     */
    private function saveLogs(Collection $logs, bool $isError = false) {
        // Catch Logs From Extension
        foreach($logs as $log) {
            // Get Step
            if($isError && !$log->isError()) {
                continue;
            }

            // Add to Log File
            $this->log->{$log->psr}($log->getLogString());
            $logs++;

            // Send Error to Slack
            if($log->isError()) {
                // TO DO: Send to Slack
                // Create a Service to Handle Slack Messages and Toggle Type
                //$this->notifySlack($msg, $psr);
            }
        }
    }
}
