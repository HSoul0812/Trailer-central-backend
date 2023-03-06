<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Exceptions\NotImplementedException;
use App\Exceptions\Marketing\Craigslist\UuidRequiredForGetClientException;
use App\Repositories\Marketing\Craigslist\ClientRepositoryInterface;
use App\Services\Marketing\Craigslist\DTOs\Client;
use App\Services\Marketing\Craigslist\DTOs\ClientMessage;
use App\Services\Marketing\Craigslist\DTOs\Behaviour;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

/**
 * Class ClientRedisRepository
 * 
 * @package App\Repositories\Marketing\Craigslist
 */
class ClientRedisRepository implements ClientRepositoryInterface
{
    /**
     * @const Default Sort Order
     */
    const DEFAULT_SORT = '-checkin';


    /**
     * Define Sort Orders
     *
     * @var array
     */
    private $sortOrders = [
        'dealer' => [
            'field' => 'dealerId',
            'direction' => 'DESC'
        ],
        '-dealer' => [
            'field' => 'dealerId',
            'direction' => 'ASC'
        ],
        'slot' => [
            'field' => 'slotId',
            'direction' => 'DESC'
        ],
        '-slot' => [
            'field' => 'slotId',
            'direction' => 'ASC'
        ],
        'checkin' => [
            'field' => 'lastCheckin',
            'direction' => 'DESC'
        ],
        '-checkin' => [
            'field' => 'lastCheckin',
            'direction' => 'ASC'
        ]
    ];

    /**
     * @var Connection|null
     */
    private $redis = null;

    /**
     * @var Log
     */
    private $log;

    public function __construct()
    {
        $this->log = Log::channel('cl-client');
    }

    /**
     * @param array $params
     * @return bool
     */
    public function create($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param array $params
     * @return bool
     */
    public function update($params)
    {
        throw new NotImplementedException;
    }

    /**
     * Get Single Client
     * 
     * @param array $params
     * @throws UuidRequiredForGetClientException
     * @return Client
     */
    public function get($params)
    {
        $this->connectToRedis();

        // Log Get
        $this->log->info('Getting client with params ', $params);

        // Get UUID
        $uuid = $params['uuid'];
        if(empty($uuid)) {
            $this->log->error('Param uuid is missing');
            throw new UuidRequiredForGetClientException;
        }

        // Get Data
        $key = 'client:' . $uuid;
        $this->log->info('Passing HGETALL ' . $key . ' to Redis');
        $clientData = $this->redis->hgetall($key);
        $this->log->info('Retrieved client details for client ID #' . $uuid . ': ', $clientData);

        // Add Port to Clients Array
        return new Client([
            'dealer_id'    => $clientData['dealerId'],
            'slot_id'      => $clientData['slotId'],
            'uuid'         => $uuid,
            'count'        => $clientData['count'],
            'version'      => $clientData['version'],
            'last_ip'      => $clientData['last-ip'],
            'last_checkin' => $clientData['last-checkin'],
            'label'        => $clientData['label']
        ]);
    }

    public function delete($params)
    {
        throw new NotImplementedException;
    }

    /**
     * Get All Clients
     * 
     * @param array $params
     * @return Collection<Client>
     */
    public function getAll($params = [])
    {
        $this->connectToRedis();

        // Get Clients Server
        $this->log->info('Getting All Clients for Params', $params);

        // Check Dealer ID
        $dealerId = '*';
        if(!empty($params['dealer_id'])) {
            $dealerId = $params['dealer_id'];
        }

        // Check Slot ID
        $slotId = '';
        if(!empty($params['slot_id'])) {
            $slotId = '.' . $params['slot_id'];
        }

        // Scan for Clients
        $key = 'client-list-all:' . $dealerId . $slotId;
        $this->log->info('Passing SCAN ' . $key . ' to Redis');
        $clientKeys = $this->redis->scan($key);
        $this->log->info('Returned ' . count($clientKeys) . ' clients in total');

        // Loop Connections
        $clients = new Collection();
        foreach($clientKeys as $key) {
            // Parse Dealer and Slot ID
            $parsed = str_replace('client-list-all:', '', $key);

            // Split Dealer ID / Slot ID
            list($dealerId, $slotId) = explode('.', $parsed);

            // Combine Dealer Clients
            $clients->merge($this->getAllUuids($dealerId, $slotId));
        }

        // Append Sort
        return $this->sort($clients, '-checkin');
    }

    /**
     * Get All Clients
     * 
     * @param array $params
     * @return Collection<Client>
     */
    public function getAllInternal(): Collection
    {
        // Get Clients Server
        $this->log->info('Getting All Internal Clients for Params');

        // Scan for Clients
        $behaviours = Behaviour::getAllInternal();
        $this->log->info('Retrieved All ' . count($behaviours) . ' Internal Behaviours');

        // Loop Behaviours
        $clients = new Collection();
        foreach($behaviours as $behaviour) {
            $ignore = explode(",", config('marketing.cl.settings.warning.ignore'));
            if(in_array($behaviour->dealerId, $ignore)) {
                continue;
            }

            // Combine Dealer Clients
            $uuids = $this->getAllUuids($behaviour->dealerId, $behaviour->slotId);
            if($uuids->count() > 0) {
                foreach($uuids as $uuid) {
                    $clients->push($uuid);
                }
            } else {
                $clients->push($behaviour);
            }
            $this->log->info('Merged ' . $uuids->count() . ' Clients For Total ' . $clients->count());
        }

        // Append Sort
        $this->log->info('Merged All ' . $clients->count() . ' Clients Into Single Collection');
        return $this->sort($clients, '-checkin');
    }


    /**
     * Was the Email Last Sent Within the Interval?
     * 
     * @param string $email
     * @param int $interval
     * @return int
     */
    public function sentIn(string $email, int $interval): int
    {
        $this->connectToRedis();

        // Redis Key Exists for Slack?
        $lastRun = $this->redis->hmget(ClientMessage::LAST_SENT_KEY, [$email]);

        // Check Interval
        return (floor((time() - (int) $lastRun[0]) / 60) > $interval);
    }

    /**
     * Mark Email's Last Sent Time to Now
     * 
     * @param string $email
     * @return void
     */
    public function markSent(string $email): void
    {
        $this->connectToRedis();

        // Set Current Time on Last Sent Key
        $this->redis->hmset(ClientMessage::LAST_SENT_KEY, $email, time());
    }


    /**
     * Get All UUID's
     * 
     * @param int $dealerId
     * @param int $slotId
     * @return Collection<Client>
     */
    private function getAllUuids(int $dealerId, int $slotId): Collection
    {
        $this->connectToRedis();

        // Get All UUID's for Dealer ID and Slot ID
        $key = 'client-list-all:' . $dealerId . '.' . $slotId;
        $this->log->info('Passing ZRANGE ' . $key . ' 0 -1 to Redis');
        $clientUuids = $this->redis->zrange($key, 0, -1);

        // Loop Client ID's
        $clients = new Collection();
        foreach($clientUuids as $uuid) {
            // Get Dealer Client
            $client = $this->get([
                'uuid' => $uuid
            ]);

            // Get Dealer Client
            $clients->push($client);
        }

        // Return Clients
        $this->log->info('Retrieved ' . $clients->count() . ' Clients For Dealer #' . $dealerId . ' and Slot #' . $slotId);
        return $clients;
    }


    /**
     * Sort Clients By Field
     * 
     * @param Collection<Client> $clients
     * @param null|string $sort
     * @return Collection<Client>
     */
    private function sort(Collection $clients, ?string $sort = null): Collection {
        // Get Order
        $order = $this->sortOrders[$sort];

        // Set Default Sort?
        if($sort === null || empty($order)) {
            $sort = self::DEFAULT_SORT;
            $order = $this->sortOrders[$sort];
        }
        $this->log->info('Sorting ' . $clients->count() . ' Clients by Field ' . $order['field']);

        // Loop Clients
        $clients->sort(function($a, $b) use($order) {
            // Get Column
            $aVal = (int) $a->{$order['field']};
            $bVal = (int) $b->{$order['field']};

            // Equal Values on Both Sides?
            if($aVal === $bVal) {
                return 0;
            }

            // Return Result
            if($order['direction'] === 'ASC') {
                return ($aVal < $bVal) ? -1 : 1;
            }
            return ($aVal > $bVal) ? -1 : 1;
        });

        // Return Result After Sort
        return $clients;
    }

    private function connectToRedis(): void
    {
        if ($this->redis instanceof Connection) {
            return;
        }

        $this->redis = Redis::connection('persist');
    }
}
