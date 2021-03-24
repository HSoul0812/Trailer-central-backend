<?php

namespace App\Repositories\Website;

use App\Exceptions\NotImplementedException;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;

/**
 * Class ProxiedDomainSslRepository
 * @package App\Repositories\Website
 */
class DealerProxyRedisRepository implements DealerProxyRepositoryInterface
{
    /**
     * @var Connection
     */
    private $redis;

    public function __construct()
    {
        $this->redis = Redis::connection('dealer-proxy');
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

        return $this->redis->set($params['domain'], $params['value']);
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

        return $this->redis->set($params['domain'], $params['value']);
    }

    public function get($params)
    {
        throw new NotImplementedException;
    }

    public function delete($params)
    {
        throw new NotImplementedException;
    }

    public function getAll($params)
    {
        throw new NotImplementedException;
    }
}
