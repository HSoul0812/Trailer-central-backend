<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Exceptions\NotImplementedException;
use App\Models\Marketing\Craigslist\ActivePost;
use App\Models\Marketing\Craigslist\Session;
use App\Models\Marketing\Craigslist\Profile;
use App\Repositories\Traits\SortTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ActivePostRepository implements ActivePostRepositoryInterface {
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
        ],
        'added' => [
            'field' => 'clapp_active_posts.added',
            'direction' => 'DESC'
        ],
        '-added' => [
            'field' => 'clapp_active_posts.added',
            'direction' => 'ASC'
        ],
        'updated' => [
            'field' => 'clapp_active_posts.updated',
            'direction' => 'DESC'
        ],
        '-updated' => [
            'field' => 'clapp_active_posts.updated',
            'direction' => 'ASC'
        ]
    ];

    /**
     * Create Facebook Page
     * 
     * @param array $params
     * @return ActivePost
     */
    public function create($params) {
        // Set Dates if Not Provided
        if(!isset($params['added'])) {
            $params['added'] = Carbon::now()->toDateTimeString();
        }
        if(!isset($params['drafted'])) {
            $params['drafted'] = $params['added'];
        }
        if(!isset($params['posted'])) {
            $params['posted'] = $params['added'];
        }

        // Create Active Post
        return ActivePost::create($params);
    }

    /**
     * Delete Page
     * 
     * @param int $id
     * @throws NotImplementedException
     */
    public function delete($id) {
        throw NotImplementedException;
    }

    /**
     * Get Active Post
     * 
     * @param array $params
     * @return ActivePost
     */
    public function get($params) {
        // CLID Exists?
        if(isset($params['clid']) && $params['clid']) {
            return ActivePost::where('clid', $params['clid'])->firstOrFail();
        }

        // Find Page By ID
        return ActivePost::findOrFail($params['id']);
    }

    /**
     * Get All Active Posts That Match Params
     * 
     * @param array $params
     * @return Collection<ActivePost>
     */
    public function getAll($params) {
        $query = ActivePost::leftJoin(Session::getTableName(), Session::getTableName().'.session_id', '=', ActivePost::getTableName().'.session_id')
                           ->leftJoin(Profile::getTableName(), Profile::getTableName().'.id', '=', ActivePost::getTableName().'.profile_id')
                           ->where(Profile::getTableName().'.dealer_id', '=', $params['dealer_id']);

        if (!isset($params['per_page'])) {
            $params['per_page'] = 5;
        }

        if (isset($params['dealer_location_id'])) {
            $query = $query->where('dealer_location_id', $params['dealer_location_id']);
        }

        if (isset($params['profile_id'])) {
            $query = $query->where(ActivePost::getTableName().'.profile_id', '=', $params['profile_id']);
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
     * Update Page
     * 
     * @param array $params
     * @return ActivePost
     */
    public function update($params) {
        $post = $this->find($params);

        DB::transaction(function() use (&$post, $params) {
            // Set Dates if Not Provided
            if(!isset($params['added']) && empty($post->added)) {
                $params['added'] = Carbon::now()->toDateTimeString();
            }
            if(!isset($params['updated'])) {
                $params['updated'] = Carbon::now()->toDateTimeString();
            }
            if(!isset($params['drafted']) && empty($post->drafted)) {
                $params['drafted'] = $post->added ?? Carbon::now()->toDateTimeString();
            }
            if(!isset($params['posted']) && empty($post->posted)) {
                $params['posted'] = $post->added ?? Carbon::now()->toDateTimeString();
            }

            // Fill Active Post Details
            $post->fill($params)->save();
        });

        return $post;
    }


    /**
     * Find ActivePost
     * 
     * @param array $params
     * @return null|ActivePost
     */
    public function find(array $params): ?ActivePost {
        // CLID Exists?
        if(isset($params['clid']) && $params['clid']) {
            return ActivePost::where('clid', $params['clid'])->first();
        }

        // Find ActivePost By ID
        return ActivePost::find($params['id'] ?? 0);
    }

    /**
     * Create OR Update ActivePost
     * 
     * @param array $params
     * @return ActivePost
     */
    public function createOrUpdate(array $params): ActivePost {
        // Get ActivePost
        $activePost = $this->find($params);

        // ActivePost Exists? Update!
        if(!empty($activePost->id)) {
            return $this->update($params);
        }

        // Create Instead
        return $this->create($params);
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }
}
