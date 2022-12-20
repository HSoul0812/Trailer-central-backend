<?php

namespace App\Repositories\Marketing\Facebook;

use App\DTO\Marketing\DealerPosting;
use App\Exceptions\NotImplementedException;
use App\Exceptions\RepositoryInvalidArgumentException;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

/**
 * Class PostingRedisRepository
 *
 * @package App\Repositories\Marketing\Facebook
 */
class PostingRedisRepository implements PostingRepositoryInterface
{
    /**
     * @const Time To Live in Seconds
     */
    const TTL = 60 * 5;

    /**
     * @const Redis Prefix Key
     */
    const REDIS_NAMESPACE = 'fb:posting:';

    /**
     * @const Default Sort Order
     */
    const DEFAULT_SORT = '-dealer';

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
        'integration' => [
            'field' => 'integrationId',
            'direction' => 'DESC'
        ],
        '-integration' => [
            'field' => 'integrationId',
            'direction' => 'ASC'
        ],
        'expiryTime' => [
            'field' => 'expiryTime',
            'direction' => 'DESC'
        ],
        '-expiryTime' => [
            'field' => 'expiryTime',
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
        $this->log = Log::channel('dispatch-fb');
    }

    /**
     * @param array $params
     * @return bool
     */
    public function create($params)
    {
        if (!isset($params['id'])) {
            throw new RepositoryInvalidArgumentException;
        }

        $this->connectToRedis();

        $this->log->info('Creating ' . self::REDIS_NAMESPACE . $params['id'] . ' expiring in ' . self::getTtl() . ' seconds');

        $this->redis->setex(self::REDIS_NAMESPACE . $params['id'], self::getTtl(), $params['dealerId'] ?? time());
    }

    /**
     * @param array $params
     * @return bool
     */
    public function update($params)
    {
        if (!isset($params['id'])) {
            throw new RepositoryInvalidArgumentException;
        }

        $this->connectToRedis();

        $this->log->info('Check if ' . self::REDIS_NAMESPACE . $params['id'] . ' has not yet expired');

        // Check if Not Yet Expired
        $key = $this->redis->get(self::REDIS_NAMESPACE . $params['id']);
        if(!empty($key)) {
            $this->create($params);
        }
    }

    /**
     * Get Single Running Session
     *
     * @param type $params
     * @throws RepositoryInvalidArgumentException
     * @return DealerPosting
     */
    public function get($params)
    {
        $this->connectToRedis();

        // Log Get
        $this->log->info('Getting running session with params ', $params);

        // Get Integration ID
        $integrationId = $params['id'];
        if(empty($integrationId)) {
            $this->log->error('Param id is missing');
            throw new RepositoryInvalidArgumentException;
        }

        // Get Data
        $key = self::REDIS_NAMESPACE . $integrationId;
        $dealerId = $this->redis->get($key);
        $expiryTime = $this->redis->executeRaw('EXPIRYTIME', $key);

        return new DealerPosting([
            'id' => $params['id'],
            'dealer_id' => $dealerId,
            'integration_id' => $integrationId,
            'expiry_time' => $expiryTime
        ]);
    }

    public function delete($params)
    {
        if (!isset($params['id'])) {
            throw new RepositoryInvalidArgumentException;
        }

        $this->connectToRedis();

        $this->redis->del(self::REDIS_NAMESPACE . $params['id']);
    }

    /**
     * Get All Current Running Session
     *
     * @param array $params
     * @return Collection<DealerPosting>
     */
    public function getAll($params = [])
    {
        $this->log->info('Getting All Running Posting Session with Params', $params);

        $integrationIds = $this->getIntegrationIds();
        $dealerPostings = new Collection();

        foreach ($integrationIds as $integrationId) {
            $dealerPosting = $this->get([
                'id' => $integrationId
            ]);
            $dealerPostings->push($dealerPosting);
        }

        // Append Sort
        return $this->sort($dealerPostings, $params['sort']);
    }

    /**
     * Get All Current Running Integration Ids
     *
     * @return Array<Integer>
     */
    public function getIntegrationIds()
    {
        $this->connectToRedis();

        $allKeys = $this->redis->keys(self::REDIS_NAMESPACE . '*');
        $integrationIds = [];

        foreach ($allKeys as $key) {
            // extract out Integration ID from key string format
            list(, , $integrationId) = explode(':', $key);
            $integrationIds[] = $integrationId;
        }
        $this->log->info('Found all active integration ID\'s: ' . implode(", ", $integrationIds));

        // Return Array of Active Integration ID's
        return $integrationIds;
    }

    /**
     * Sort Collection By Field
     *
     * @param Collection<DealerPosting> $collections
     * @param null|string $sort
     * @return Collection<DealerPosting>
     */
    private function sort(Collection $collections, ?string $sort = null): Collection {
        // Get Order
        $order = $this->sortOrders[$sort];

        // Set Default Sort?
        if($sort === null || empty($order)) {
            $sort = self::DEFAULT_SORT;
            $order = $this->sortOrders[$sort];
        }
        $this->log->info('Sorting ' . $collections->count() . ' data by Field ' . $order['field']);

        // Loop Collection
        $collections->sort(function($a, $b) use($order) {
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
        return $collections;
    }



    /**
     * Get TTL From Constant or Config Vars
     *
     * @return int
     */
    public static function getTtl(): int {
        // Find Config
        $ttl = config('marketing.fb.settings.limit.ttl');
        if(!empty($ttl) || $ttl === '0') {
            return 60 * (int) $ttl;
        }

        // Return Constant
        return self::TTL;
    }

    private function connectToRedis(): void
    {
        if ($this->redis instanceof Connection) {
            return;
        }

        $this->redis = Redis::connection('persist');

        $this->log->info('Initialized Redis FB Posting Using ' . $this->redis->getName());
        $this->log->info('Found Keys: ', $this->redis->keys(self::REDIS_NAMESPACE . '*'));
    }
}
