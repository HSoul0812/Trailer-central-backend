<?php

namespace App\Repositories\Marketing;

use App\DTO\Marketing\DealerTunnel;
use App\Exceptions\NotImplementedException;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

/**
 * Class TunnelRedisRepository
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

    public function __construct()
    {
        $this->redis = Redis::connection();
    }

    /**
     * @param array $params
     * @return bool
     */
    public function create($params)
    {
        if (!isset($params['domain'])) {
            throw new \InvalidArgumentException('Domain param is missed');
        }

        if (!isset($params['value']) || !is_bool($params['value'])) {
            throw new \InvalidArgumentException('Value param is missed');
        }
        
        throw new NotImplementedException;
        //return $this->redis->set($params['domain'], $params['value']);
    }

    /**
     * @param array $params
     * @return bool
     */
    public function update($params)
    {
        if (!isset($params['domain'])) {
            throw new \InvalidArgumentException('Domain param is missed');
        }

        if (!isset($params['value']) || !is_bool($params['value'])) {
            throw new \InvalidArgumentException('Value param is missed');
        }

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
        // Get Dealer ID
        $dealerId = $params['dealer_id'];
        if(empty($dealerId)) {
            throw new DealerIdRequiredForGetTunnelException;
        }

        // Get Tunnel ID
        $tunnelId = $params['id'];
        if(empty($tunnelId)) {
            throw new IdRequiredForGetTunnelException;
        }

        // Get Tunnels Server
        $server = $params['tunnel_server'] ?? self::SERVER_DEFAULT;

        // Get Data
        $key = 'tunnels:info:' . $server . ':' . $dealerId . ':' . $tunnelId;
        $tunnelData = $this->redis->hgetall($key);

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
    public function getAll($params)
    {
        // Get Dealer ID
        $dealerId = $params['dealer_id'];

        // Get Tunnels Server
        $server = $params['tunnel_server'] ?? self::SERVER_DEFAULT;

        // Get Tunnels By Dealer
        $tunnelIds = $this->redis->smembers('tunnels:byDealerId:' . $server . ':' . $dealerId);

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

        // Append Sort
        return $this->sort($dealerTunnels, '-ping');
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