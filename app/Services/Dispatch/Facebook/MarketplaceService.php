<?php

namespace App\Services\Dispatch\Facebook;

use App\Models\User\AuthToken;
use App\Models\User\Integration\Integration;
use App\Models\Marketing\Facebook\Marketplace;
use App\Models\Marketing\Facebook\Listings;
use App\Models\Marketing\Facebook\Error;
use App\Repositories\Marketing\TunnelRepositoryInterface;
use App\Repositories\Marketing\Facebook\MarketplaceRepositoryInterface;
use App\Repositories\Marketing\Facebook\ListingRepositoryInterface;
use App\Repositories\Marketing\Facebook\ImageRepositoryInterface;
use App\Repositories\Marketing\Facebook\ErrorRepositoryInterface;
use App\Repositories\Marketing\Facebook\PostingRepositoryInterface;
use App\Services\Dispatch\Facebook\DTOs\DealerFacebook;
use App\Services\Dispatch\Facebook\DTOs\InventoryFacebook;
use App\Services\Dispatch\Facebook\DTOs\MarketplaceInventory;
use App\Services\Dispatch\Facebook\DTOs\MarketplaceStatus;
use App\Services\Dispatch\Facebook\DTOs\MarketplaceStep;
use App\Transformers\Dispatch\Facebook\InventoryTransformer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection as Pagination;
use Illuminate\Support\Arr;

/**
 * Class MarketplaceService
 * 
 * @package App\Services\Dispatch\Facebook
 */
class MarketplaceService implements MarketplaceServiceInterface
{
    /**
     * @const Facebook Messenger Integration Name
     */
    const INTEGRATION_NAME = 'dispatch_facebook';


    /**
     * @var MarketplaceRepositoryInterface
     */
    protected $marketplace;

    /**
     * @var TunnelRepositoryInterface
     */
    protected $tunnels;

    /**
     * @var PostingRepositoryInterface
     */
    protected $postingSession;

    /**
     * Construct Facebook Marketplace Service
     * 
     * @param MarketplaceRepositoryInterface $marketplace
     * @param TunnelRepositoryInterface $tunnels
     * @param ListingRepositoryInterface $listings
     * @param ImageRepositoryInterface $images
     * @param ErrorRepositoryInterface $errors
     * @param PostingRepositoryInterface $postingSession
     * @param InventoryTransformer $inventoryTransformer
     */
    public function __construct(
        MarketplaceRepositoryInterface $marketplace,
        TunnelRepositoryInterface $tunnels,
        ListingRepositoryInterface $listings,
        ImageRepositoryInterface $images,
        ErrorRepositoryInterface $errors,
        PostingRepositoryInterface $postingSession,
        InventoryTransformer $inventoryTransformer
    ) {
        $this->marketplace = $marketplace;
        $this->tunnels = $tunnels;
        $this->listings = $listings;
        $this->images = $images;
        $this->errors = $errors;
        $this->postingSession = $postingSession;

        // Initialize Inventory Transformer
        $this->inventoryTransformer = $inventoryTransformer;

        // Initialize Logger
        $this->log = Log::channel('dispatch-fb');
    }


    /**
     * Login to Marketplace
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

        // Get Integration Name
        $integration = Integration::where('name', self::INTEGRATION_NAME)->first();

        // Get Auth Token
        $token = AuthToken::where(['user_type' => 'integration', 'user_id' => $integration->id])->first();

        // Return Access Token
        return $token->access_token;
    }

    /**
     * Get Marketplace Status
     * 
     * @return MarketplaceStatus
     */
    public function status(): MarketplaceStatus {
        // Get All Marketplace Integration Dealers
        $dealers = $this->getIntegrations();

        // Get Available Tunnels
        $tunnels = $this->tunnels->getAll();

        // Return MarketplaceStatus
        return new MarketplaceStatus([
            'dealers' => $dealers,
            'tunnels' => $tunnels
        ]);
    }


    /**
     * Get Dealer Inventory
     * 
     * @param int $integrationId
     * @param array $params
     * @return DealerFacebook
     */
    public function dealer(int $integrationId, array $params, ?float $startTime = null): DealerFacebook {
        // Get Integration
        if(empty($startTime)) {
            $startTime = microtime(true);
        }
        $integration = $this->marketplace->get([
            'id' => $integrationId
        ]);

        // Get Types
        $type = !empty($params['type']) ? $params['type'] : MarketplaceInventory::METHOD_DEFAULT;
        if(empty(MarketplaceStatus::INVENTORY_METHODS[$type])) {
            $type = MarketplaceInventory::METHOD_DEFAULT;
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
            'auth_type' => $integration->tfa_type,
            'tunnels' => $this->tunnels->getAll(['dealer_id' => $integration->dealer_id]),
            'inventory' => !$integration->is_up_to_date ? $this->getInventory($integration, $type, $params) : null
        ]);
        $nowTime = microtime(true);
        $this->log->info('Debug time after creating DealerFacebook: ' . ($nowTime - $startTime));
        return $response;
    }

    /**
     * Login to Marketplace
     * 
     * @param array $params
     * @return Listings
     */
    public function create(array $params): Listings {
        // Log
        $this->log->info('Created Facebook Marketplace Inventory #' .
                            $params['facebook_id'] . ' with the TC' .
                            ' Inventory #' . $params['inventory_id'] .
                            ' for the Marketplace Integration #' . $params['id']);

        // Start Transaction
        $this->listings->beginTransaction();

        try {
            // Insert Into DB
            $listing = $this->listings->create($params);
            $this->log->info('Saved Listing #' . $listing->id . ' for ' .
                                'Facebook Listing #' . $params['facebook_id']);

            // Create Images for Listing
            if(!empty($params['images']) && is_array($params['images'])) {
                // Delete Existing Images for Listing
                $this->images->deleteAll($listing->id);

                // Add New Images
                foreach($params['images'] as $imageId) {
                    $this->images->create([
                        'listing_id' => $listing->id,
                        'image_id' => $imageId
                    ]);
                }
                $this->log->info('Saved ' . count($params['images']) . ' Images for ' .
                                    'Listing #' . $params['id']);
            }

            $nrOfListingsToday = $this->listings->countFacebookPostings(Marketplace::find($params['marketplace_id']));
            $inventoryRemaining = $this->getInventory(Marketplace::find($params['marketplace_id']), MarketplaceStatus::METHOD_MISSING, []);
            $nrInventoryItemsRemaining = count(Arr::get($inventoryRemaining, 'inventory', []));

            if ($nrOfListingsToday === config('marketing.fb.settings.limit.listings', 3) || $nrInventoryItemsRemaining === 0) {
                // Update Imported At
                $marketplace = $this->marketplace->update([
                    'id' => $params['marketplace_id'],
                    'imported_at' => Carbon::now()->setTimezone('UTC')->toDateTimeString()
                ]);
            }

            $this->listings->commitTransaction();

            // Return Listing
            return $listing;
        } catch (Exception $e) {
            $this->log->error('Marketplace Listing create error. params=' .
                                    json_encode($params), $e->getTrace());

            $this->listings->rollbackTransaction();

            throw $e;
        }
    }

    /**
     * Logging Details for Step
     * 
     * @param MarketplaceStep $step
     * @return MarketplaceStep
     */
    public function step(MarketplaceStep $step): MarketplaceStep {
        // Log Step Response
        $this->log->info($step->getResponse());

        // Handle Logs
        $this->saveLogs($step->getLogs(), $step->isError());

        // Create Error
        $this->reportError($step);

        try {
            // add marketplace_id to session
            if ($step->isLogin()) {
                $this->postingSession->create([
                    'id' => $step->marketplaceId
                ]);
            }
            // remove marketplace_id from session
            elseif ($step->isLogout() || $step->isStop()) {
                $this->postingSession->delete([
                    'id' => $step->marketplaceId
                ]);
            }
            // update marketplace_id on session
            else {
                $this->postingSession->update([
                    'id' => $step->marketplaceId
                ]);
            }
        } catch (\Exception $e) {
            $this->log->error('Error occurred during updating step for fb marketplace ' .
                                    '#' . $step->marketplaceId, $e->getTrace());
        }

        // Return Listing
        return $step;
    }


    /**
     * Get Dealer Integrations
     * 
     * @return Collection<DealerFacebook>
     */
    private function getIntegrations(): Collection {
        
        $runningIntegrationIds = $this->postingSession->getIntegrationIds();

        $integrations = $this->marketplace->getAll(['sort' => '-last_attempt_ts',
            'import_range' => config('marketing.fb.settings.limit.hours', 0),
            'exclude' => $runningIntegrationIds,
            'skip_errors' => config('marketing.fb.settings.limit.errors', 1)
        ]);

        // Loop Facebook Integrations
        $dealers = new Collection();
        foreach($integrations as $integration) {
            $dealers->push(new DealerFacebook([
                'dealer_id' => $integration->dealer_id,
                'dealer_location_id' => $integration->dealer_location_id,
                'dealer_name' => $integration->user->name,
                'integration_id' => $integration->id,
                'fb_username' => $integration->fb_username,
                'fb_password' => $integration->fb_password,
                'auth_username' => $integration->tfa_username,
                'auth_password' => $integration->tfa_password,
                'auth_type' => $integration->tfa_type,
                'tunnels' => $this->tunnels->getAll(['dealer_id' => $integration->dealer_id]),
                'last_attempt_ts' => $integration->last_attempt_ts
            ]));
        }

        // Return Dealers Collection
        return $dealers;
    }

    /**
     * Get Inventory to Post
     * 
     * @param Marketplace $integration
     * @param string $type missing|updates|sold
     * @param array $params
     * @return Pagination<InventoryFacebook>
     */
    private function getInventory(Marketplace $integration, string $type, array $params, ?float $startTime = null): MarketplaceInventory {
        // Invalid Type? Return Empty Collection!
        if(empty($startTime)) {
            $startTime = microtime(true);
        }
        if(!isset(MarketplaceStatus::INVENTORY_METHODS[$type])) {
            return new Pagination();
        }

        // Get Method
        $method = MarketplaceStatus::INVENTORY_METHODS[$type];

        if ($type === MarketplaceStatus::METHOD_MISSING) {
            $params['per_page'] = config('marketing.fb.settings.limit.listings') - $this->listings->countFacebookPostings($integration);
        }

        // Get Inventory
        $inventory = $this->listings->{$method}($integration, $params);
        $nowTime = microtime(true);
        $this->log->info('Debug time after ' . $method . ': ' . ($nowTime - $startTime));

        // Loop Through Inventory Items
        $listings = new Collection();
        foreach($inventory as $listing) {
            if($type === MarketplaceStatus::METHOD_MISSING) {
                $item = InventoryFacebook::getFromInventory($listing, $integration);
            } else {
                $item = InventoryFacebook::getFromListings($listing);
            }
            $listings->push($item);
            $nowTime = microtime(true);
            $this->log->info('Debug time InventoryFacebook #' . $listing->inventory_id . ': ' . ($nowTime - $startTime));
        }

        // Append Paginator
        $response = new MarketplaceInventory([
            'type' => $type,
            'inventory' => $listings,
            'paginator' => new IlluminatePaginatorAdapter($inventory)
        ]);
        return $response;
    }

    /**
     * Save Logs to Dispatch Logs
     * 
     * @param Marketplace $integration
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
     * @param MarketplaceStep $step
     * @return null|Error
     */
    private function reportError(MarketplaceStep $step): ?Error {
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