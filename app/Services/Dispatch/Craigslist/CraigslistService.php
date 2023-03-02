<?php

namespace App\Services\Dispatch\Craigslist;

use App\Models\User\AuthToken;
use App\Models\User\Integration\Integration;
use App\Repositories\Marketing\TunnelRepositoryInterface;
use App\Repositories\Marketing\VirtualCardRepositoryInterface;
use App\Repositories\Marketing\Craigslist\AccountRepositoryInterface;
use App\Repositories\Marketing\Craigslist\DealerRepositoryInterface;
use App\Repositories\Marketing\Craigslist\ProfileRepositoryInterface;
use App\Services\Dispatch\Craigslist\DTOs\DealerCraigslist;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use League\Fractal\Resource\Collection as Pagination;

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
     * @const array<string> Craigslist Dealer Available Includes
     */
    const AVAILABLE_INCLUDES = [
        'accounts',
        'profiles',
        'cards',
        'tunnels'
    ];


    /**
     * @var DealerRepositoryInterface
     */
    protected $dealers;

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
        AccountRepositoryInterface $accounts,
        ProfileRepositoryInterface $profiles,
        VirtualCardRepositoryInterface $cards,
        TunnelRepositoryInterface $tunnels
    ) {
        $this->dealers = $dealers;
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
        $dealerClapp = [
            'dealer_id'    => $clapp->dealer_id,
            'slots'        => $clapp->slots,
            'chrome_mode'  => $clapp->chrome_mode,
            'since'        => $clapp->since,
            'next'         => $clapp->next_session,
            'dealer_name'  => $clapp->dealer->name,
            'dealer_email' => $clapp->dealer->email,
            'dealer_type'  => $clapp->dealer->type,
            'dealer_state' => $clapp->dealer->state
        ];

        // Include Extra Features
        foreach(self::AVAILABLE_INCLUDES as $include) {
            if(!empty($params['include']) && strpos($include, $params['include']) !== false) {
                $dealerClapp[$include] = $this->$include->getAll(['dealer_id' => $dealerId]);
            }
        }
        $response = new DealerCraigslist($dealerClapp);

        // Log Time After Returning Results
        $nowTime = microtime(true);
        $this->log->info('Debug time after creating DealerFacebook: ' . ($nowTime - $startTime));
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
     * @param Craigslist $integration
     * @param string $type missing|updates|sold
     * @param array $params
     * @return Pagination<InventoryFacebook>
     */
    private function getInventory(Craigslist $integration, string $type, array $params, ?float $startTime = null): CraigslistInventory {
        // Invalid Type? Return Empty Collection!
        if(empty($startTime)) {
            $startTime = microtime(true);
        }
        if(!isset(CraigslistStatus::INVENTORY_METHODS[$type])) {
            return new Pagination();
        }

        // Get Method
        $method = CraigslistStatus::INVENTORY_METHODS[$type];

        $nowTime = microtime(true);
        $this->log->info('Debug time BEFORE ' . $method . ': ' . ($nowTime - $startTime));

        if ($type === CraigslistStatus::METHOD_MISSING) {
            $maxListings = $integration->posts_per_day ?? config('marketing.fb.settings.limit.listings', 3);
            $params['per_page'] = $maxListings - $this->listings->countFacebookPostings($integration);
        }

        // Get Inventory
        $inventory = $this->listings->{$method}($integration, $params);

        $nowTime = microtime(true);
        $this->log->info('Debug time after ' . $method . ': ' . ($nowTime - $startTime));

        // Loop Through Inventory Items
        $listings = new Collection();
        foreach ($inventory as $listing) {
            if ($type === CraigslistStatus::METHOD_MISSING) {
                $item = InventoryFacebook::getFromInventory($listing, $integration);
            } else {
                $item = InventoryFacebook::getFromListings($listing);
            }

            $listings->push($item);
            $nowTime = microtime(true);
            $this->log->info('Debug time InventoryFacebook #' . $listing->inventory_id . ': ' . ($nowTime - $startTime));
        }

        // Append Paginator
        $response = new CraigslistInventory([
            'type' => $type,
            'inventory' => $listings
        ]);
        return $response;
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
