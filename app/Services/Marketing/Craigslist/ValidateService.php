<?php

namespace App\Services\Marketing\Craigslist;

use App\Repositories\Marketing\Craigslist\ClientRepositoryInterface;
use App\Services\Marketing\Craigslist\DTOs\Behaviour;
use App\Services\Marketing\Craigslist\DTOs\Client;
use App\Services\Marketing\Craigslist\DTOs\ClientMessage;
use App\Services\Marketing\Craigslist\DTOs\ClientValidate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Class ValidateService
 * 
 * @package App\Services\Marketing\Craigslist
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
        'clients.edit',
        'counts.warning',
        'counts.critical'
    ];

    /**
     * @var ClientRepositoryInterface
     */
    protected $repo;

    /**
     * @var SchedulerRepositoryInterface
     */
    protected $scheduler;

    /**
     * Construct Facebook Marketplace Service
     * 
     * @param ClientRepositoryInterface $repo
     * @param SchedulerRepositoryInterface $scheduler
     */
    public function __construct(
        ClientRepositoryInterface $repo,
        SchedulerRepositoryInterface $scheduler
    ) {
        $this->repo = $repo;
        $this->scheduler = $scheduler;

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
        $this->log->info('Cl Client Elapsed ' . $client->elapsed() . ' Minutes, Last Checked In on ' . $client->lastCheckin);

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
     * Validate Behaviours With All Clients Expired
     * 
     * @param Behaviour $behaviour
     * @return ClientValidate
     */
    public function expired(Behaviour $behaviour): ClientValidate {
        // No Active Clients For The Given Behaviour, Its AUTOMATICALLY Critical
        $level = 'critical';

        // Report Log Level
        $this->log->info('Behaviour ' . $behaviour->email . ' Has No Active Clients!');

        // Return ClientValidate
        return new ClientValidate([
            'dealer_id' => $behaviour->dealerId,
            'slot_id'   => $behaviour->slotId,
            'uuid'      => $behaviour->uuid,
            'email'     => $behaviour->email,
            'label'     => 'Unknown',
            'isEdit'    => $behaviour->isEdit,
            'level'     => $level,
            'elapsed'   => Client::MAX_ELAPSED
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
            if(empty($client->email)) {
                continue;
            }
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
     * Count Posts Needed to be Sent
     * 
     * @return null|ClientMessage
     */
    public function count(): ?ClientMessage {
        // Get Past Due Scheduled Posts
        $duePast = $this->scheduler->duePast();
        $this->log->info('Cl Scheduler Currently has ' . $duePast . ' Posts Due to be Submitted Now');

        // Get Warning From Past Due
        $level = 'info';
        $config = $this->getConfig();
        if($duePast > (int) $config['counts.warning']) {
            $level = 'warning';
        }

        // Get Critical From Past Due
        if($duePast > (int) $config['counts.critical']) {
            $level = 'critical';
        }
        $this->log->info('Cl Scheduler Counts Reported a Log Level of ' . $level);

        // Get Remaining Scheduled Posts
        $dueToday = $this->scheduler->dueToday();
        $this->log->info('Cl Scheduler Currently has ' . $dueToday . ' Posts Due to be Submitted The Rest of the Day');

        // Get Client Message
        $message = ClientMessage::counts($level, $duePast, $dueToday);

        // Return ClientMessage
        if($this->send($message)) {
            return $message;
        }
        return null;
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
        if(!$this->repo->sentIn($message->email, $interval)) {
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
     * @param array<string: array<ClientValidate>> $active
     * @param array<string: array<ClientValidate>> $warnings
     * @return Collection<ClientMessage>
     */
    private function messages(array $active, array $warnings): Collection {
        // Check All Warning Clients
        $messages = new Collection();
        foreach($warnings as $email => $warning) {
            // Get Config
            $valid = $active[$email] ?? [];
            $client = $warning[0];
            $config = $this->getConfig($client->dealerId);
            $message = '';

            // Check Number of Clients
            if(count($valid) < 1) {
                $message = ClientMessage::varied(collect($warning));
            } elseif($client->isEdit) {
                if(count($valid) <= (int) $config['clients.edit']) {
                    $message = ClientMessage::warning(collect($valid));
                }
            } elseif(count($valid) <= (int) $config['clients.low']) {
                $message = ClientMessage::warning(collect($valid));
            }

            // Message Exists?
            if(!empty($message)) {
                $messages->push($message);
                if(isset($active[$email])) {
                    unset($active[$email]);
                }
            }
        }

        // Find Remaining Active Accounts
        foreach($active as $email => $validation) {
            $messages->push(ClientMessage::active(collect($validation)));
        }

        // Return Messages
        return $messages;
    }


    /**
     * Get Config From Environment Variables
     * 
     * @param null|int
     * @return array{string: string}
     */
    private function getConfig(?int $dealerId): array {
        // Loop Config Paths
        $config = [];
        foreach(self::CONFIG_PATHS as $path) {
            $value = config('marketing.cl.settings.warning.' . $path);

            // Check Override Instead
            if(!empty($dealerId)) {
                $override = $this->getOverride($path, $dealerId);
                if(!empty($override)) {
                    $value = $override;
                }
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
        $config = config('marketing.cl.settings.warning.override.' . $path);
        if(empty($config)) {
            return '';
        }

        // Parse Overrides
        $overrides = explode(';', $config);
        $clean = [];
        foreach($overrides as $override) {
            if(strpos($override, ':') === FALSE) {
                continue;
            }
            list($dealer, $value) = explode(':', $override);
            $clean[$dealer] = $value;
        }

        // Return Clean Override Array
        return $clean[$dealerId] ?? '';
    }
}
