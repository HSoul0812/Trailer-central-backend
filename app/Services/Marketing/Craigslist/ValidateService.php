<?php

namespace App\Services\Marketing\Craigslist;

use App\Repositories\Marketing\Craigslist\ClientRepositoryInterface;
use App\Services\Marketing\Craigslist\DTOs\Client;
use App\Services\Marketing\Craigslist\DTOs\ClientMessage;
use App\Services\Marketing\Craigslist\DTOs\ClientValidate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Class ValidateService
 * 
 * @package App\Services\Marketing\Facebook
 */
class ValidateService implements ValidateServiceInterface
{
    /**
     * @const Config Paths
     */
    const CONFIG_PATHS = [
        'enabled',
        'ignore',
        'elapse.warning',
        'elapse.error',
        'elapse.critical',
        'clients.low',
        'clients.edit'
    ];

    /**
     * @var ProfileRepositoryInterface
     */
    protected $profiles;

    /**
     * Construct Facebook Marketplace Service
     * 
     * @param ClientRepositoryInterface $repo
     */
    public function __construct(
        ClientRepositoryInterface $repo
    ) {
        $this->repo = $repo;

        // Create Marketplace Logger
        $this->log = Log::channel('cl-client');
        $this->slack = Log::channel('slack-cl');
    }

    /**
     * Validate Provided Client
     * 
     * @param Client $client
     * @return ClientValidate
     */
    public function validate(Client $client): ClientValidate {
        // Get Config
        $config = $this->getConfig($client->dealerId);

        // Is a Warning?
        $level = 'info';
        if($client->elapsed() > (int) $config['elapse.warning']) {
            $level = 'warning';
        }

        // Is an Error?
        if($client->elapsed() > (int) $config['elapse.error']) {
            $level = 'error';
        }

        // Is Critical?
        if($client->elapsed() > (int) $config['elapse.critical']) {
            $level = 'critical';
        }

        // Report Log Level
        $this->log->info('Cl Client ' . $client->label . ' Reported a Log Level of ' . $level);

        // Return ClientValidate
        return new ClientValidate([
            'dealer_id' => $client->dealerId,
            'slot_id'   => $client->slotId,
            'uuid'      => $client->uuid,
            'email'     => $client->email(),
            'label'     => $client->label,
            'isEdit'    => $client->isEdit(),
            'level'     => $level,
            'elapsed'   => $client->elapsed()
        ]);
    }

    /**
     * Return Status of All Clients
     * 
     * @param Collection<ClientValidate> $validation
     * @return Collection<ClientMessage>
     */
    public function status(Collection $validation): Collection {
        // Initialize Mapping Array
        $active = [];
        $warnings = [];

        // Loop All Validated Clients
        foreach($validation as $client) {
            // Level is a Warning?
            if($client->isWarning()) {
                if(!isset($warnings[$client->email])) {
                    $warnings[$client->email] = [];
                }
                $warnings[$client->email][] = $client;
            }
            // Level is Info?
            else {
                if(!isset($active[$client->email])) {
                    $active[$client->email] = [];
                }
                $active[$client->email][] = $client;
            }
        }

        // Report Warnings / Accounts
        $this->log->info(count($active) . ' Active Clients and ' . count($warnings) . ' Inactive Clients Reported for CL Schedulers.');

        // Return Collection<ClientMessage)
        return $this->messages($active, $warnings);
    }

    /**
     * Validate if Its Time to Send Message Now, If So, Send It
     * 
     * @param ClientMessage $message
     * @return bool
     */
    public function send(ClientMessage $message): bool {
        // Check if Message Sent Recently
        $interval = (int) config('marketing.cl.settings.slack.interval');

        // Check if Message Sent Recently
        if(!$this->repo->sentIn($interval, $message->email)) {
            $this->slack->{$message->level}($message->message);
            $this->repo->markSent($message->email);
            $this->log->info('Successfully Sent ' . $message->level . ' Slack Message for ' . $message->email);
            return true;
        }

        // Not Sent
        $this->log->info('Could Not Send ' . ucfirst($message->level) . ' Slack Message for ' .
                            $message->email . ' Because It Has Not Been ' . $interval .
                            ' Minutes Since Last Slack Message');
        return false;
    }


    /**
     * Recover Messages From Accounts Active and w/Warnings
     * 
     * @param array<ClientValidate> $active
     * @param array<ClientValidate> $warnings
     * @return Collection<ClientMessage>
     */
    private function messages(array $active, array $warnings): Collection {
        // Check All Warning Clients
        $messages = new Collection();
        foreach($warnings as $email => $warning) {
            // Get Config
            $valid = $active[$email];
            $client = $warning[0];
            $config = $this->getConfig($client->dealerId);

            // Check Number of Clients
            if(count($active) < 1) {
                $message = ClientMessage::varied($warning);
            } elseif($client->isEdit && count($valid) <= (int) $config['clients.edit']) {
                $message = ClientMessage::warning($valid);
            } elseif(count($valid) <= (int) $config['clients.low']) {
                $message = ClientMessage::warning($valid);
            }

            // Message Exists?
            if(!empty($message)) {
                $messages->push($message);
                unset($active[$email]);
            }
        }

        // Find Remaining Active Accounts
        foreach($active as $email => $single) {
            $messages->push(ClientMessage::active($single));
        }

        // Return Messages
        return $messages;
    }


    /**
     * Get Config From Environment Variables
     * 
     * @param int
     * @return array{string: string}
     */
    private function getConfig(int $dealerId): array {
        // Loop Config Paths
        $config = [];
        foreach(self::CONFIG_PATHS as $path) {
            $value = config('marketing.cl.warning.' . $path);

            // Check Override Instead
            $override = $this->getOverride($path, $dealerId);
            if(!empty($override)) {
                $value = $override;
            }

            // Add Config
            $config[$path] = $value;
        }

        // Return Config Array
        return $config;
    }

    /**
     * Get Override Config From Environment Variables
     * 
     * @param string
     * @param int
     * @return string
     */
    private function getOverride(string $path, int $dealerId): string {
        // Loop Config Paths
        $config = config('marketing.cl.warning.override.' . $path);

        // Parse Overrides
        $overrides = explode(';', $config);
        $clean = [];
        foreach($overrides as $override) {
            list($dealer, $value) = explode(':', $override);
            $clean[$dealer] = $value;
        }

        // Return Clean Override Array
        return $clean[$dealerId];
    }
}
