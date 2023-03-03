<?php

namespace App\Repositories\Marketing\Craigslist;

use Illuminate\Support\Facades\DB;
use App\Exceptions\NotImplementedException;
use App\Models\Marketing\Craigslist\Queue;
use App\Models\Marketing\Craigslist\Session;
use App\Models\Marketing\Craigslist\Profile;
use App\Repositories\Traits\SortTrait;

class QueueRepository implements QueueRepositoryInterface {
    use SortTrait;

    /**
     * Define Sort Orders
     *
     * @var array
     */
    private $sortOrders = [
        'queued' => [
            'field' => 'clapp_queue.time',
            'direction' => 'DESC'
        ],
        '-queued' => [
            'field' => 'clapp_queue.time',
            'direction' => 'ASC'
        ]
    ];

    /**
     * Create Queue
     * 
     * @param array $params
     * @return Queue
     */
    public function create($params) {
        // Create Queue
        return Queue::create($params);
    }

    /**
     * Delete Queue
     * 
     * @param int $id
     * @throws NotImplementedException
     */
    public function delete($id) {
        throw NotImplementedException;
    }

    /**
     * Get Queue
     * 
     * @param array $params
     * @return Queue
     */
    public function get($params) {
        // Queue ID Exists?
        if(isset($params['queue_id']) && $params['queue_id']) {
            return Queue::where('queue_id', $params['queue_id'])->firstOrFail();
        }

        // Find Queue By ID
        return Queue::findOrFail($params['id']);
    }

    /**
     * Get All Queues That Match Params
     * 
     * @param array $params
     * @return Collection<Queue>
     */
    public function getAll($params) {
        $query = Queue::leftJoin(Session::getTableName(), Session::getTableName().'.session_id', '=', Queue::getTableName().'.session_id')
                           ->leftJoin(Profile::getTableName(), Profile::getTableName().'.id', '=', Queue::getTableName().'.profile_id');

        if (!isset($params['per_page'])) {
            $params['per_page'] = 5;
        }

        if (isset($params['dealer_location_id'])) {
            $query = $query->where('dealer_location_id', $params['dealer_location_id']);
        }

        if (isset($params['profile_id'])) {
            $query = $query->where(Queue::getTableName().'.profile_id', '=', $params['profile_id']);
        }

        if (isset($params['slot_id'])) {
            $query = $query->where(Session::getTableName() . '.session_slot_id', $params['slot_id']);
        }

        if (isset($params['id'])) {
            $query = $query->whereIn('id', $params['id']);
        }

        if(!isset($params['sort'])) {
            $params['sort'] = 'added';
        }
        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }
        
        return $query->with('inventory')->with('inventory.orderedImages')
                     ->paginate($params['per_page'])->appends($params);
    }

    /**
     * Update Queue
     * 
     * @param array $params
     * @return Queue
     */
    public function update($params) {
        $queue = $this->get($params);

        DB::transaction(function() use (&$queue, $params) {
            // Fill Session Details
            $queue->fill($params)->save();
        });

        return $queue;
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }
}