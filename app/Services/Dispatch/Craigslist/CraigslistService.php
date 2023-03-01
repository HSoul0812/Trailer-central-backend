<?php

namespace App\Services\Dispatch\Craigslist;

use App\Models\User\AuthToken;
use App\Models\User\Integration\Integration;
use App\Repositories\Marketing\TunnelRepositoryInterface;
use App\Repositories\Marketing\Craigslist\TunnelRepositoryInterface;
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
    const INTEGRATION_NAME = 'craigslist_poster';


    /**
     * @var DealerRepositoryInterface
     */
    protected $dealers;

    /**
     * @var TunnelRepositoryInterface
     */
    protected $tunnels;

    /**
     * Construct Craigslist Dispatch Service
     *
     * @param DealerRepositoryInterface $dealers
     * @param TunnelRepositoryInterface $tunnels
     */
    public function __construct(
        DealerRepositoryInterface $dealers,
        TunnelRepositoryInterface $tunnels
    ) {
        $this->dealers = $dealers;
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
     * @return Coilection<DealerStatus>
     */
    public function status(array $params): Collection {
        // Get All Craigslist Dealers
        return $this->getDealers($params);
    }


    /**
     * Get Dealer Inventory
     *
     * @param int $integrationId
     * @param array $params
     * @return DealerFacebook
     */
    /*public function dealer(int $integrationId, array $params, ?float $startTime = null): DealerFacebook {
        // Get Integration
        if(empty($startTime)) {
            $startTime = microtime(true);
        }
        $integration = $this->marketplace->get([
            'id' => $integrationId
        ]);

        // Get Types
        $type = !empty($params['type']) ? $params['type'] : CraigslistInventory::METHOD_DEFAULT;
        if (empty(CraigslistStatus::INVENTORY_METHODS[$type])) {
            $type = CraigslistInventory::METHOD_DEFAULT;
        }

        // Get Facebook Dealer
        $response = new DealerFacebook([
            'dealer_id' => $integration->dealer_id,
            'dealer_location_id' => $integration->dealer_location_id,
            'dealer_name' => $integration->user->name,
            'integration_id' => $integrationId,
            'fb_username' => $integration->fb_username,
            'fb_password' => $integration->fb_password,
            'auth_username' => $integration->tfa_username,
            'auth_password' => $integration->tfa_password,
            'auth_code' => $integration->tfa_code,
            'auth_type' => $integration->tfa_type,
            'tunnels' => $this->tunnels->getAll(['dealer_id' => $integration->dealer_id]),
            'missing' => !$integration->is_up_to_date ? $this->getInventory($integration, 'missing', $params) : null,
            'sold' => !$integration->is_up_to_date ? $this->getInventory($integration, 'sold', $params) : null,
            'posts_per_day' => $integration->posts_per_day ?? intval(config('marketing.fb.settings.limit.listings', 3))

        ]);
        $nowTime = microtime(true);
        $this->log->info('Debug time after creating DealerFacebook: ' . ($nowTime - $startTime));
        return $response;
    }*/


    /**
     * Get Dealers Signed Up with Craigslist
     *
     * @return Collection<DealerCraigslist>
     */
    private function getDealers(array $params): Collection {
        // Get Craigslist Dealers
        $dealerClapp = $this->dealers->getAll([
            'sort' => '-date_scheduled',
            'import_range' => config('marketing.fb.settings.limit.hours', 0),
            'skip_errors' => config('marketing.fb.settings.limit.errors', 1),
            'per_page' => $params['per_page'] ?? null,
            'type' => $params['type']
        ]);

        // Loop Facebook Integrations
        $dealers = new Collection();
        foreach($dealerClapp as $clapp) {
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

    /**
     * Save Logs to Dispatch Logs
     *
     * @param CraigslistStep $step
     * @return null|Error
     */
    private function reportError(CraigslistStep $step): ?Error {
        // Create New FB Error From Returned Step Details
        if($step->isError()) {
            // Dismiss Existing Errors
            $this->errors->dismissAll($step->marketplaceId, $step->inventoryId ?? 0);

            // Return Error
            return $this->errors->create([
                'marketplace_id' => $step->marketplaceId,
                'inventory_id' => $step->inventoryId,
                'action' => $step->action,
                'step' => $step->step,
                'error_type' => $step->getErrorType(),
                'error_message' => $step->message,
                'expires_at' => $step->getExpiryTime()
            ]);
        }

        // No Error, Return Null
        return null;
    }
}
