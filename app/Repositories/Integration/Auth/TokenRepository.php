<?php

namespace App\Repositories\Integration\Auth;

use Illuminate\Support\Facades\DB;
use App\Exceptions\NotImplementedException;
use App\Models\Integration\Auth\AccessToken;
use Carbon\Carbon;

class TokenRepository implements TokenRepositoryInterface {
    /**
     * Define Sort Orders
     *
     * @var array
     */
    private $sortOrders = [
        'issued_at' => [
            'field' => 'issued_at',
            'direction' => 'DESC'
        ],
        '-issued_at' => [
            'field' => 'issued_at',
            'direction' => 'ASC'
        ],
        'expires_at' => [
            'field' => 'expires_at',
            'direction' => 'DESC'
        ],
        '-expires_at' => [
            'field' => 'expires_at',
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
     * Create (or Update) Access Token
     * 
     * @param array $params
     * @return AccessToken
     */
    public function create($params) {
        // Access Token Already Exists?
        $token = $this->find($params);

        // Access Token Found?
        if(!empty($token->id)) {
            return $this->update($params);
        }

        // Return Access Token
        return AccessToken::create($params);
    }

    /**
     * Delete Access Token
     * 
     * @param array $params
     * @throws NotImplementedException
     */
    public function delete($params) {
        throw new NotImplementedException;
    }

    /**
     * Get Access Token
     * 
     * @param array $params
     * @return AccessToken
     */
    public function get($params) {
        // Find Token By ID
        return AccessToken::findOrFail($params['id']);
    }

    /**
     * Get Access Token Via Relation
     * 
     * @param array $params
     * @return AccessToken
     */
    public function getRelation($params) {
        // Find Token From Relation
        return AccessToken::where('token_type', $params['token_type'])
                          ->where('relation_type', $params['relation_type'])
                          ->where('relation_id', $params['relation_id'])
                          ->first();
    }

    /**
     * Get All Access Tokens That Match Params
     * 
     * @param array $params
     * @return Collection of Access Token
     */
    public function getAll($params) {
        $query = AccessToken::where('id', '>', 0);
        
        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        if (isset($params['token_type']) && isset($params['relation_type']) && isset($params['relation_id'])) {
            $query = $query->where('token_type', $params['token_type'])
                           ->where('relation_type', $params['relation_type'])
                           ->where('relation_id', $params['relation_id']);
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
     * Update Access Token
     * 
     * @param array $params
     * @return AccessToken
     */
    public function update($params) {
        $token = $this->find($params);

        DB::transaction(function() use (&$token, $params) {
            // Fill Text Details
            $token->fill($params)->save();
        });

        return $token;
    }

    /**
     * Find Exact Match Access Token
     * 
     * @param array $params
     * @return QueryBuilder
     */
    public function find($params) {
        // Token ID Exists?
        if (isset($params['id'])) {
            return AccessToken::findOrFail($params['id']);
        }

        // Relation Exists?
        if (isset($params['token_type']) && isset($params['relation_type']) && isset($params['relation_id'])) {
            $accessToken = AccessToken::where('token_type', $params['token_type'])
                                      ->where('relation_type', $params['relation_type'])
                                      ->where('relation_id', $params['relation_id'])
                                      ->first();

            // Return Access Token
            if(!empty($accessToken->id)) {
                return $accessToken;
            }
        }

        // Return Empty
        return null;
    }


    /**
     * Add Sort Query
     * 
     * @param type $query
     * @param type $sort
     * @return type
     */
    private function addSortQuery($query, $sort) {
        if (!isset($this->sortOrders[$sort])) {
            return;
        }

        return $query->orderBy($this->sortOrders[$sort]['field'], $this->sortOrders[$sort]['direction']);
    }
}
