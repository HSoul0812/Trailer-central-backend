<?php

namespace App\Services\Dispatch\Facebook;

use App\Http\Requests\Dispatch\Facebook\CreateMarketplaceRequest;
use App\Models\User\AuthToken;
use App\Models\User\Integration\Integration;
use App\Models\Marketing\Facebook\Marketplace;
use App\Repositories\Marketing\TunnelRepositoryInterface;
use App\Repositories\Marketing\Facebook\MarketplaceRepositoryInterface;
use App\Repositories\Marketing\Facebook\ListingRepositoryInterface;
use App\Services\Dispatch\Facebook\DTOs\DealerFacebook;
use App\Services\Dispatch\Facebook\DTOs\MarketplaceStatus;
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
     */
    public function __construct(
        MarketplaceRepositoryInterface $marketplace,
        TunnelRepositoryInterface $tunnels,
        ListingRepositoryInterface $listings
    ) {
        $this->marketplace = $marketplace;
        $this->tunnels = $tunnels;
        $this->listings = $listings;

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
     * @param CreateMarketplaceRequest $request
     * @return Listings
     */
    public function create(CreateMarketplaceRequest $request): Listings {
        // Log
        $this->log->info('Created Facebook Marketplace Inventory #' .
                            $request->facebook_id . ' with the TC' .
                            ' Inventory #' . $request->inventory_id .
                            ' for the Marketplace Integration #' . $request->id);

        // Insert Into DB
        $listing = $this->listings->create($request->all());
        $this->log->info('Saved Listing #' . $listing->id . ' for ' .
                            'Facebook Listing #' . $request->facebook_id);

        // Create Images for Listing
        if($request->images && is_array($request->images)) {
            foreach($request->images as $imageId) {
                $this->images->create([
                    'listing_id' => $listing->id,
                    'image_id' => $imageId
                ]);
            }
            $this->log->info('Saved ' . count($request->images) . ' for ' .
                                'Listing #' . $request->id);
        }

        // Return Listing
        return $listing;
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


    /**
     * Catch Logs and Errors
     * 
     * @param null|array $logs
     * @param null|bool $restrict
     */
    private function catchLogs(?array $logs = null, ?bool $restrict = null) {
        // Catch All Logs
        foreach($logs as $psr => $data) {
            // Restrict Logs to One Type
            if($restrict !== null && $restrict !== $psr) {
                continue;
            }

            // Create String
            $msg = '';
            if(is_array($data)) {
                foreach($data as $item) {
                    $msg .= ((is_array($item) || is_object($item)) ? print_r($item, true) : $item);
                }
            }

            // Add to Log File
            $this->log->{$psr}($msg);

            // Send Error to Slack
            if($psr === 'error') {
                // TO DO: Send to Slack
                // Create a Service to Handle Slack Messages and Toggle Type
                //$this->notifySlack($msg, $psr);
            }
        }
    }
}