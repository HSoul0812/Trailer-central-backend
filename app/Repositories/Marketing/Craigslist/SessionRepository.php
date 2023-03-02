<?php

namespace App\Repositories\Marketing\Craigslist;

use Illuminate\Support\Facades\DB;
use App\Exceptions\NotImplementedException;
use App\Models\Marketing\Craigslist\Queue;
use App\Models\Marketing\Craigslist\Session;
use App\Models\Marketing\Craigslist\Profile;
use App\Repositories\Traits\SortTrait;

class SessionRepository implements SessionRepositoryInterface {
    use SortTrait;

    /**
     * Define Sort Orders
     *
     * @var array
     */
    private $sortOrders = [
        'scheduled' => [
            'field' => 'clapp_session.clapp_scheduled',
            'direction' => 'DESC'
        ],
        '-scheduled' => [
            'field' => 'clapp_session.clapp_scheduled',
            'direction' => 'ASC'
        ]
    ];

    /**
     * Create Facebook Session
     * 
     * @param array $params
     * @return Session
     */
    public function create($params) {
        // Create Active Session
        return Session::create($params);
    }

    /**
     * Delete Session
     * 
     * @param int $id
     * @throws NotImplementedException
     */
    public function delete($id) {
        throw NotImplementedException;
    }

    /**
     * Get Active Session
     * 
     * @param array $params
     * @return Session
     */
    public function get($params) {
        // CLID Exists?
        if(isset($params['session_id']) && $params['session_id']) {
            return Session::where('session_id', $params['session_id'])->firstOrFail();
        }

        // Find Session By ID
        return Session::findOrFail($params['id']);
    }

    /**
     * Get All Active Sessions That Match Params
     * 
     * @param array $params
     * @return Collection<Session>
     */
    public function getAll($params) {
        $query = Session::leftJoin(Queue::getTableName(), Session::getTableName().'.session_id', '=', Queue::getTableName().'.session_id')
                           ->leftJoin(Profile::getTableName(), Queue::getTableName().'.id', '=', Queue::getTableName().'.profile_id');

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
     * Update Session
     * 
     * @param array $params
     * @return Session
     */
    public function update($params) {
        $session = $this->get($params);

        DB::transaction(function() use (&$session, $params) {
            // Fill Active Session Details
            $session->fill($params)->save();
        });

        return $session;
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }
}