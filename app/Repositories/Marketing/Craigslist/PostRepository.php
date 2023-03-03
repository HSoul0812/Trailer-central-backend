<?php

namespace App\Repositories\Marketing\Craigslist;

use Illuminate\Support\Facades\DB;
use App\Exceptions\NotImplementedException;
use App\Models\Marketing\Craigslist\Post;
use App\Models\Marketing\Craigslist\Session;
use App\Models\Marketing\Craigslist\Profile;
use App\Repositories\Traits\SortTrait;

class PostRepository implements PostRepositoryInterface {
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
            'field' => 'clapp_posts.added',
            'direction' => 'DESC'
        ],
        '-added' => [
            'field' => 'clapp_posts.added',
            'direction' => 'ASC'
        ],
        'updated' => [
            'field' => 'clapp_posts.updated',
            'direction' => 'DESC'
        ],
        '-updated' => [
            'field' => 'clapp_posts.updated',
            'direction' => 'ASC'
        ]
    ];

    /**
     * Create Facebook Page
     * 
     * @param array $params
     * @return Post
     */
    public function create($params) {
        // Create Active Post
        return Post::create($params);
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
     * @return Post
     */
    public function get($params) {
        // CLID Exists?
        if(isset($params['clid']) && $params['clid']) {
            return Post::where('clid', $params['clid'])->firstOrFail();
        }

        // Find Page By ID
        return Post::findOrFail($params['id']);
    }

    /**
     * Get All Active Posts That Match Params
     * 
     * @param array $params
     * @return Collection<Post>
     */
    public function getAll($params) {
        $query = Post::leftJoin(Session::getTableName(), Session::getTableName().'.session_id', '=', Post::getTableName().'.session_id')
                           ->leftJoin(Profile::getTableName(), Profile::getTableName().'.id', '=', Post::getTableName().'.profile_id')
                           ->where(Profile::getTableName().'.dealer_id', '=', $params['dealer_id']);

        if (!isset($params['per_page'])) {
            $params['per_page'] = 5;
        }

        if (isset($params['dealer_location_id'])) {
            $query = $query->where('dealer_location_id', $params['dealer_location_id']);
        }

        if (isset($params['profile_id'])) {
            $query = $query->where(Post::getTableName().'.profile_id', '=', $params['profile_id']);
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
     * @return Post
     */
    public function update($params) {
        $post = $this->get($params);

        DB::transaction(function() use (&$post, $params) {
            // Fill Active Post Details
            $post->fill($params)->save();
        });

        return $post;
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }
}