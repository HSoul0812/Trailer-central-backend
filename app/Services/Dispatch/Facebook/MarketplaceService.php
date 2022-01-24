<?php

namespace App\Services\Dispatch\Facebook;

use App\Models\User\AuthToken;
use App\Models\User\Integration\Integration;
use App\Models\Marketing\Facebook\Marketplace;
use App\Models\Marketing\Facebook\Listings;
use App\Repositories\Marketing\TunnelRepositoryInterface;
use App\Repositories\Marketing\Facebook\MarketplaceRepositoryInterface;
use App\Repositories\Marketing\Facebook\ListingRepositoryInterface;
use App\Repositories\Marketing\Facebook\ImageRepositoryInterface;
use App\Services\Dispatch\Facebook\DTOs\DealerFacebook;
use App\Services\Dispatch\Facebook\DTOs\InventoryFacebook;
use App\Services\Dispatch\Facebook\DTOs\MarketplaceInventory;
use App\Services\Dispatch\Facebook\DTOs\MarketplaceStatus;
use App\Services\Dispatch\Facebook\DTOs\MarketplaceStep;
use App\Transformers\Dispatch\Facebook\InventoryTransformer;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use League\Fractal\Resource\Collection as Pagination;

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
     * Construct Facebook Marketplace Service
     * 
     * @param MarketplaceRepositoryInterface $marketplace
     * @param TunnelRepositoryInterface $tunnels
     * @param ListingRepositoryInterfaces $listings
     * @param ImageRepositoryInterfaces $images
     */
    public function __construct(
        MarketplaceRepositoryInterface $marketplace,
        TunnelRepositoryInterface $tunnels,
        ListingRepositoryInterface $listings,
        ImageRepositoryInterface $images,
        InventoryTransformer $inventoryTransformer
    ) {
        $this->marketplace = $marketplace;
        $this->tunnels = $tunnels;
        $this->listings = $listings;
        $this->images = $images;

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
            'inventory' => $this->getInventory($integration, $type, $params)
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
                $this->log->info('Saved ' . count($params['images']) . ' for ' .
                                    'Listing #' . $params['id']);
            }

            $this->listings->commitTransaction();

            // Return Listing
            return $listing;
        } catch (Exception $e) {
            $this->logger->error('Marketplace Listing create error. params=' .
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
        // Log
        $this->log->info($step->getResponse());

        // Catch Logs From Extension
        foreach($step->getLogs() as $log) {
            // Get Step
            if($step->isError() && !$log->isError()) {
                continue;
            }

            // Add to Log File
            $this->log->{$log->psr}($log->getLogString());

            // Send Error to Slack
            if($log->isError()) {
                // TO DO: Send to Slack
                // Create a Service to Handle Slack Messages and Toggle Type
                //$this->notifySlack($msg, $psr);
            }
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
        $integrations = $this->marketplace->getAll([
            'sort' => '-imported'
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
                'tunnels' => $this->tunnels->getAll(['dealer_id' => $integration->dealer_id])
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

        // Get Inventory
        $inventory = $this->listings->{$method}($integration, $params);
        $nowTime = microtime(true);
        $this->log->info('Debug time after ' . $method . ': ' . ($nowTime - $startTime));

        // Loop Through Inventory Items
        $listings = new Collection();
        foreach($inventory as $listing) {
            if($type === MarketplaceStatus::METHOD_MISSING) {
                $listings->push(InventoryFacebook::getFromInventory($listing, $integration));
            } else {
                $listings->push(InventoryFacebook::getFromListings($listing));
            }
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
}