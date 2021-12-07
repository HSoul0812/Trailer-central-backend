<?php

namespace App\Services\Dispatch\Facebook;

use App\Http\Requests\Dispatch\Facebook\CreateMarketplaceRequest;
use App\Models\User\AuthToken;
use App\Models\User\Integration\Integration;
use App\Models\Marketing\Facebook\Marketplace;
use App\Models\Marketing\Facebook\Listings;
use App\Repositories\Marketing\TunnelRepositoryInterface;
use App\Repositories\Marketing\Facebook\MarketplaceRepositoryInterface;
use App\Repositories\Marketing\Facebook\ListingRepositoryInterface;
use App\Repositories\Marketing\Facebook\ImageRepositoryInterface;
use App\Services\Dispatch\Facebook\DTOs\DealerFacebook;
use App\Services\Dispatch\Facebook\DTOs\MarketplaceStatus;
use App\Services\Dispatch\Facebook\DTOs\MarketplaceStep;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

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
        ImageRepositoryInterface $images
    ) {
        $this->marketplace = $marketplace;
        $this->tunnels = $tunnels;
        $this->listings = $listings;
        $this->images = $images;

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

        // Return Listing
        return $listing;
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
            'sort' => '-username'
        ]);

        // Loop Facebook Integrations
        $dealers = new Collection();
        foreach($integrations as $integration) {
            $dealers->push(new DealerFacebook([
                'dealer_id' => $integration->dealer_id,
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
}