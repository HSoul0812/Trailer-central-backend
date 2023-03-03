<?php

namespace App\Services\Dispatch\Craigslist;

use App\Models\User\AuthToken;
use App\Models\User\Integration\Integration;
use App\Repositories\Marketing\TunnelRepositoryInterface;
use App\Repositories\Marketing\VirtualCardRepositoryInterface;
use App\Repositories\Marketing\Craigslist\AccountRepositoryInterface;
use App\Repositories\Marketing\Craigslist\DealerRepositoryInterface;
use App\Repositories\Marketing\Craigslist\ProfileRepositoryInterface;
use App\Repositories\Marketing\Craigslist\SchedulerRepositoryInterface;
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
     * @var DealerRepositoryInterface
     */
    protected $dealers;

    /**
     * @var SchedulerRepositoryInterface
     */
    protected $scheduler;

    /**
     * @var AccountRepositoryInterface
     */
    protected $accounts;

    /**
     * @var ProfileRepositoryInterface
     */
    protected $profiles;

    /**
     * @var VirtualRepositoryInterface
     */
    protected $cards;

    /**
     * @var TunnelRepositoryInterface
     */
    protected $tunnels;

    /**
     * Construct Craigslist Dispatch Service
     *
     * @param DealerRepositoryInterface $dealers
     * @param AccountRepositoryInterface $accounts
     * @param ProfileRepositoryInterface $profiles
     * @param VirtualCardRepositoryInterface $cards
     * @param TunnelRepositoryInterface $tunnels
     */
    public function __construct(
        DealerRepositoryInterface $dealers,
        SchedulerRepositoryInterface $scheduler,
        AccountRepositoryInterface $accounts,
        ProfileRepositoryInterface $profiles,
        VirtualCardRepositoryInterface $cards,
        TunnelRepositoryInterface $tunnels
    ) {
        $this->dealers = $dealers;
        $this->scheduler = $scheduler;
        $this->accounts = $accounts;
        $this->profiles = $profiles;
        $this->cards = $cards;
        $this->tunnels = $tunnels;

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
            $this->queues->update($queue->getParams());
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
     * @return bool
     */
    private function markError(Queue $queue, string $error): bool {
        // Get Error Status
        $err = ClappError::fill($error);

        // Update Session
        $post = $this->sessions->update([
            'session_id' => $queue->session_id,
            'status' => $err->status,
            'state' => $err>state,
            'text_status' => $err->textStatus
        ]);

        // Session Was Changed?
        return $post->wasChanged();
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
