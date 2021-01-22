<?php

namespace App\Repositories\System;

use Illuminate\Support\Facades\DB;
use App\Models\System\Email;
use App\Exceptions\NotImplementedException;
use App\Repositories\Traits\SortTrait;

class EmailRepository implements EmailRepositoryInterface {
    use SortTrait;

    /**
     * Define Sort Orders
     *
     * @var array
     */
    private $sortOrders = [
        'email' => [
            'field' => 'email',
            'direction' => 'DESC'
        ],
        '-email' => [
            'field' => 'email',
            'direction' => 'ASC'
        ],
        'created_at' => [
            'field' => 'created_at',
            'direction' => 'DESC'
        ],
        '-created_at' => [
            'field' => 'created_at',
            'direction' => 'ASC'
        ],
        'updated_at' => [
            'field' => 'updated_at',
            'direction' => 'DESC'
        ],
        '-updated_at' => [
            'field' => 'updated_at',
            'direction' => 'ASC'
        ]
    ];

    /**
     * Create System Email
     * 
     * @param array $params
     * @return System Email
     */
    public function create($params) {
        // Create System Email
        return Email::create($params);
    }

    /**
     * Delete System Email
     * 
     * @param int $id
     * @throws NotImplementedException
     */
    public function delete($id) {
        // Delete System Email
        return Email::findOrFail($id)->delete();
    }

    /**
     * Get System Email
     * 
     * @param array $params
     * @return System Email
     */
    public function get($params) {
        // Find System Email By ID
        return Email::findOrFail($params['id']);
    }

    /**
     * Get All System Emails That Match Params
     * 
     * @param array $params
     * @return Collection of System Emails
     */
    public function getAll($params) {
        $query = Email::where('dealer_id', '=', $params['dealer_id']);
        
        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        if (isset($params['email'])) {
            $query = $query->where('email', $params['email']);
        }

        if (isset($params['id'])) {
            $query = $query->whereIn('id', $params['id']);
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }
        
        return $query->paginate($params['per_page'])->appends($params);
    }

    /**
     * Update System Email
     * 
     * @param array $params
     * @return System Email
     */
    public function update($params) {
        $email = Email::findOrFail($params['id']);

        DB::transaction(function() use (&$email, $params) {
            // Fill System Email Details
            $email->fill($params)->save();
        });

        return $email;
    }

    /**
     * Find System Email
     * 
     * @param array $params
     * @return Collection of System Emails
     */
    public function find($params) {
        $query = Email::where('id', '>', 0);

        if (isset($params['id'])) {
            $query = $query->where('id', $params['id']);
        } elseif (isset($params['email'])) {
            $query = $query->where('email', $params['email']);
        }

        return $query->first();
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }
}
