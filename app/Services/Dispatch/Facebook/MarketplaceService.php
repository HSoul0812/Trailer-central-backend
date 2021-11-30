<?php

namespace App\Services\Dispatch\Facebook;

use App\Models\User\AuthToken;
use App\Models\User\Integration\Integration;
use App\Models\Marketing\Facebook\Marketplace;
use App\Repositories\Marketing\TunnelRepositoryInterface;
use App\Repositories\Marketing\Facebook\MarketplaceRepositoryInterface;
use App\Services\Dispatch\Facebook\DTOs\MarketplaceStatus;
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
     */
    public function __construct(
        MarketplaceRepositoryInterface $marketplace,
        TunnelRepositoryInterface $tunnels
    ) {
        $this->marketplace = $marketplace;
        $this->tunnels = $tunnels;

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
        $tunnels = $this->getTunnels($dealers);

        // Return MarketplaceStatus
        return new MarketplaceStatus([
            'dealers' => $dealers,
            'tunnels' => $tunnels
        ]);
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
        foreach($integrations as $dealer) {
            $dealers->push(new DealerFacebook([
                'id' => $dealer->id,
                'dealer_id' => $dealer->dealer_id,
                'dealer_name' => $dealer->user->name,
                'fb_username' => $dealer->fb_username,
                'fb_password' => $dealer->fb_password,
                'auth_username' => $dealer->tfa_username,
                'auth_password' => $dealer->tfa_password,
                'auth_type' => $dealer->tfa_type,
                'tunnels' => $this->repository(['dealer_id' => $dealer->dealer_id])
            ]));
        }

        // Return Dealers Collection
        return new $dealers;
    }

    /**
     * Get Dealer Tunnels
     * 
     * @param Collection<DealerFacebook>
     * @return Collection<DealerTunnel>
     */
    private function getTunnels(Collection $integrations): Collection {
        // Get Unique Dealers
        $dealers = [];
        foreach($integrations as $integration) {
            if(!in_array($integration->dealerId, $dealers)) {
                $dealers[] = $integration->dealerId;
            }
        }

        // Get All Tunnels for All Dealers
        $tunnels = new Collection();
        foreach($dealers as $dealer) {
            $tunnels->merge($this->repository(['dealer_id' => $dealer->dealer_id]));
        }

        // Return Collection<DealerTunnel>
        return $tunnels;
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