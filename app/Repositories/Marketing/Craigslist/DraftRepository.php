<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Exceptions\NotImplementedException;
use App\Models\Marketing\Craigslist\Draft;
use App\Repositories\Traits\SortTrait;
use Illuminate\Support\Facades\DB;

class DraftRepository implements DraftRepositoryInterface {

    use SortTrait;

    /**
     * Define Sort Orders
     *
     * @var array
     */
    private $sortOrders = [
        'draft' => [
            'field' => 'draft',
            'direction' => 'DESC'
        ],
        '-draft' => [
            'field' => 'draft',
            'direction' => 'ASC'
        ],
        'last_updated' => [
            'field' => 'last_updated',
            'direction' => 'DESC'
        ],
        '-last_updated' => [
            'field' => 'last_updated',
            'direction' => 'ASC'
        ]
    ];

    /**
     * Create Draft
     * 
     * @param array $params
     * @return Draft
     */
    public function create($params) {
        // Create Draft
        return Draft::create($params);
    }

    /**
     * Delete Draft
     * 
     * @param string $code
     * @throws NotImplementedException
     */
    public function delete($code) {
        throw NotImplementedException;
    }

    /**
     * Get Draft
     * 
     * @param array $params
     * @return Draft
     */
    public function get($params) {
        // Find Draft By ID
        return Draft::findOrFail($params['id']);
    }

    /**
     * Get All Draft That Match Params
     * 
     * @param array $params
     * @return Collection<Draft>
     */
    public function getAll($params) {
        $query = Draft::where('id', '>', 0);

        if (isset($params['session_id'])) {
            $query = $query->where('session_id', $params['session_id']);
        }

        if (isset($params['queue_id'])) {
            $query = $query->where('queue_id', $params['queue_id']);
        }

        if (isset($params['profile_id'])) {
            $query = $query->where('profile_id', $params['profile_id']);
        }

        if (isset($params['inventory_id'])) {
            $query = $query->where('inventory_id', $params['inventory_id']);
        }

        if (!isset($params['per_page'])) {
            $params['per_page'] = 1000;
        }

        if(!isset($params['sort'])) {
            $params['sort'] = '-created_at';
        }
        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    /**
     * Update Draft
     * 
     * @param array $params
     * @return Draft
     */
    public function update($params) {
        $draft = $this->find($params);

        DB::transaction(function() use (&$draft, $params) {
            // Fill Draft Details
            $draft->fill($params)->save();
        });

        return $draft;
    }


    /**
     * Find Draft
     * 
     * @param array $params
     * @return null|Draft
     */
    public function find(array $params): ?Draft {
        // Find Draft By ID
        return Draft::find($params['id'] ?? 0);
    }

    /**
     * Create OR Update Draft
     * 
     * @param array $params
     * @return Draft
     */
    public function createOrUpdate(array $params): Draft {
        // Get Draft
        $draft = $this->find($params);

        // Draft Exists? Update!
        if(!empty($draft->id)) {
            return $this->update($params);
        }

        // Create Instead
        return $this->create($params);
    }


    protected function getSortOrders() {
        return $this->sortOrders;
    }
}