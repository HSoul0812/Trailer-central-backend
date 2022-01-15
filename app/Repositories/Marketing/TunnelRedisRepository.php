<?php

namespace App\Repositories\Marketing;

use App\DTO\Marketing\DealerTunnel;
use App\Exceptions\NotImplementedException;
use App\Repositories\Marketing\TunnelRepositoryInterface;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

/**
 * Class TunnelRedisRepository
 * 
 * @package App\Repositories\Marketing
 */
class TunnelRedisRepository implements TunnelRepositoryInterface
{
    /**
     * @const Default Sort Order
     */
    const DEFAULT_SORT = '-port';


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
        'port' => [
            'field' => 'port',
            'direction' => 'DESC'
        ],
        '-port' => [
            'field' => 'port',
            'direction' => 'ASC'
        ],
        'ping' => [
            'field' => 'lastPing',
            'direction' => 'DESC'
        ],
        '-ping' => [
            'field' => 'lastPing',
            'direction' => 'ASC'
        ]
    ];


    /**
     * @var Connection
     */
    private $redis;

    /**
     * @var Log
     */
    private $log;

    public function __construct()
    {
        $this->log = Log::channel('tunnels');
        $this->redis = Redis::connection();
        $client = $this->redis->client();
        var_dump($client->getOptions());
        $this->log->info('Initialized Redis on TunnelRedisRepository ' . $this->redis->getName());
    }

    /**
     * @param array $params
     * @return bool
     */
    public function create($params)
    {
        // Log Create
        $this->log->info('Creating tunnel with params ', $params);

        // Domain is Required
        if (!isset($params['domain'])) {
            $this->log->error('Param domain is missing');
            throw new \InvalidArgumentException('Domain param is missing');
        }

        // Value is Required
        if (!isset($params['value']) || !is_bool($params['value'])) {
            $this->log->error('Param value is missing');
            throw new \InvalidArgumentException('Value param is missing');
        }

        // Throw NotImplementedException
        throw new NotImplementedException;
        //return $this->redis->set($params['domain'], $params['value']);
    }

    /**
     * @param array $params
     * @return bool
     */
    public function update($params)
    {
        // Log Update
        $this->log->info('Updating tunnel with params ', $params);

        // Domain is Required
        if (!isset($params['domain'])) {
            $this->log->error('Param domain is missing');
            throw new \InvalidArgumentException('Domain param is missing');
        }

        // Value is Required
        if (!isset($params['value']) || !is_bool($params['value'])) {
            $this->log->error('Param value is missing');
            throw new \InvalidArgumentException('Value param is missing');
        }

        // Throw NotImplementedException
        throw new NotImplementedException;
        //return $this->redis->set($params['domain'], $params['value']);
    }

    /**
     * Get Single Tunnel
     * 
     * @param type $params
     * @throws DealerIdRequiredForGetTunnelException
     * @return DealerTunnel
     */
    public function get($params)
    {
        // Log Get
        $this->log->info('Getting tunnel with params ', $params);

        // Get Dealer ID
        $dealerId = $params['dealer_id'];
        if(empty($dealerId)) {
            $this->log->error('Param dealer_id is missing');
            throw new DealerIdRequiredForGetTunnelException;
        }

        // Get Tunnel ID
        $tunnelId = $params['id'];
        if(empty($tunnelId)) {
            $this->log->error('Param id is missing');
            throw new IdRequiredForGetTunnelException;
        }

        // Get Tunnels Server
        $server = $params['tunnel_server'] ?? self::SERVER_DEFAULT;

        // Get Data
        $key = 'tunnels:info:' . $server . ':' . $dealerId . ':' . $tunnelId;
        $this->log->info('Passing HGETALL ' . $key . ' to Redis');
        $tunnelData = $this->redis->hgetall($key);
        $this->log->info('Retrieved tunnel details for tunnel ID #' . $tunnelId . 
                            'on Dealer ID #' . $dealerId. ': ', $tunnelData);

        // Add Port to Tunnels Array
        return new DealerTunnel([
            'id' => $params['id'],
            'dealer_id' => $dealerId,
            'port' => $tunnelData['port'],
            'last_ping' => $tunnelData['lastPingAt']
        ]);
    }

    public function delete($params)
    {
        throw new NotImplementedException;
    }

    /**
     * Get All Tunnels For Dealer
     * 
     * @param array $params
     * @return Collection<DealerTunnel>
     */
    public function getAll($params = [])
    {
        // Get Tunnels Server
        $server = $params['tunnel_server'] ?? self::SERVER_DEFAULT;
        $this->log->info('Getting All Tunnels for Server ' . $server . ' with Params', $params);

        // Get By Dealer ID?
        if(isset($params['dealer_id'])) {
            $dealerTunnels = $this->getByDealer($params['dealer_id'], $server);
        } else {
            // Get Tunnels By Dealer
            $key = 'tunnels:all:' . $server;
            $this->log->info('Passing SMEMBERS ' . $key . ' to Redis');
            $tunnelIds = $this->redis->smembers($key);
            $this->log->info('Returned ' . count($tunnelIds) . ' tunnels in ' .
                                'Total on server ' . $server, $tunnelIds);

            // Loop Tunnel ID's
            $tunnels = [];
            $dealerTunnels = new Collection();
            foreach($tunnelIds as $pair) {
                // Get Dealer/Tunnel
                list($dealerId, $tunnelId) = explode(':', $pair);

                // Get Dealer Tunnel
                $dealerTunnel = $this->get([
                    'tunnel_server' => $server,
                    'dealer_id' => $dealerId,
                    'id' => $tunnelId
                ]);

                // Port Exists?
                if(in_array($dealerTunnel->port, $tunnels)) {
                    continue;
                }

                // Get Dealer Tunnel
                $dealerTunnels->push($dealerTunnel);
                $tunnels[] = $dealerTunnel->port;
            }
        }

        // Append Sort
        return $this->sort($dealerTunnels, '-ping');
    }

    /**
     * Get All Tunnels For Dealer
     * 
     * @param array $params
     * @return Collection<DealerTunnel>
     */
    public function getByDealer(int $dealerId, string $server = self::SERVER_DEFAULT): Collection
    {
        // Get Tunnels By Dealer
        $key = 'tunnels:byDealerId:' . $server . ':' . $dealerId;
        $this->log->info('Passing SMEMBERS ' . $key . ' to Redis');
        $tunnelIds = $this->redis->smembers($key);
        $this->log->info('Returned ' . count($tunnelIds) . ' tunnels for Dealer #' .
                            $dealerId . ' on server ' . $server, $tunnelIds);

        // Loop Tunnel ID's
        $tunnels = [];
        $dealerTunnels = new Collection();
        foreach($tunnelIds as $tunnelId) {
            // Get Dealer Tunnel
            $dealerTunnel = $this->get([
                'tunnel_server' => $server,
                'dealer_id' => $dealerId,
                'id' => $tunnelId
            ]);

            // Port Exists?
            if(in_array($dealerTunnel->port, $tunnels)) {
                continue;
            }

            // Get Dealer Tunnel
            $dealerTunnels->push($dealerTunnel);
        }

        // Return
        return $dealerTunnels;
    }


    /**
     * Sort Tunnels By Field
     * 
     * @param Collection<DealerTunnel> $tunnels
     * @param null|string $sort
     * @return Collection<DealerTunnel>
     */
    private function sort(Collection $tunnels, ?string $sort = null): Collection {
        // Get Order
        $order = $this->sortOrders[$sort];

        // Set Default Sort?
        if($sort === null || empty($order)) {
            $sort = self::DEFAULT_SORT;
            $order = $this->sortOrders[$sort];
        }
        $this->log->info('Sorting ' . $tunnels->count() . ' Tunnels by Field ' . $order['field']);

        // Loop Tunnels
        $tunnels->sort(function($a, $b) use($order) {
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
        return $tunnels;
    }
}