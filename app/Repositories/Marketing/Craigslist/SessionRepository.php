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
            'field' => 'clapp_session.session_scheduled',
            'direction' => 'DESC'
        ],
        '-scheduled' => [
            'field' => 'clapp_session.session_scheduled',
            'direction' => 'ASC'
        ],
        'updated' => [
            'field' => 'clapp_session.session_last_activity',
            'direction' => 'DESC'
        ],
        '-updated' => [
            'field' => 'clapp_session.session_last_activity',
            'direction' => 'ASC'
        ],
        'web_activity' => [
            'field' => 'clapp_session.webui_last_activity',
            'direction' => 'DESC'
        ],
        '-web_activity' => [
            'field' => 'clapp_session.webui_last_activity',
            'direction' => 'ASC'
        ],
        'dispatch_activity' => [
            'field' => 'clapp_session.dispatch_last_activity',
            'direction' => 'DESC'
        ],
        '-dispatch_activity' => [
            'field' => 'clapp_session.dispatch_last_activity',
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
        // Create Session
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
     * Get Session
     * 
     * @param array $params
     * @return Session
     */
    public function get($params) {
        // Session ID Exists?
        if(isset($params['session_id']) && $params['session_id']) {
            return Session::where('session_id', $params['session_id'])->firstOrFail();
        }

        // Find Session By ID
        return Session::findOrFail($params['id']);
    }

    /**
     * Get All Sessions That Match Params
     * 
     * @param array $params
     * @return Collection<Session>
     */
    public function getAll($params) {
        $query = Session::leftJoin(Queue::getTableName(), Session::getTableName().'.session_id', '=', Queue::getTableName().'.session_id')
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

        if (isset($params['client_id'])) {
            $query = $query->where(Session::getTableName() . '.session_client', $params['client_id']);
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
            // Fill Session Details
            $session->fill($params)->save();
        });

        return $session;
    }


    /**
     * Find Session
     * 
     * @param array $params
     * @return null|Session
     */
    public function find(array $params): ?Session {
        // Session ID Exists?
        if(isset($params['session_id']) && $params['session_id']) {
            return Session::where('session_id', $params['session_id'])->first();
        }

        // Find Session By ID
        return Session::find($params['id']);
    }

    /**
     * Create OR Update Session
     * 
     * @param array $params
     * @return Session
     */
    public function createOrUpdate(array $params): Session {
        // Get Session
        $post = $this->find($params);

        // Session Exists? Update!
        if(!empty($post->id)) {
            return $this->update($params);
        }

        // Create Instead
        return $this->create($params);
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }
}