<?php

namespace App\Repositories\Integration\Auth;

use Illuminate\Support\Facades\DB;
use App\Exceptions\NotImplementedException;
use App\Models\Integration\Auth\AccessToken;
use App\Models\Integration\Auth\Scope;
use Illuminate\Database\Eloquent\Collection;
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
        $accessToken = AccessToken::create($params);

        // Update Scopes
        if(isset($params['scopes'])) {
            $this->updateScopes($accessToken->id, $params['scopes']);
        }

        // Return Access Token
        return $this->find($params);
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
        throw new NotImplementedException();
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

            // Update Scopes
            if(isset($params['scopes'])) {
                $this->updateScopes($token->id, $params['scopes']);
            }
        });

        return $this->find($params);
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
     * Delete Access Token Scopes
     * 
     * @param int $tokenId
     * @param array $scopes
     * @return Collection of Scopes
     */
    private function updateScopes($tokenId, $scopes) {
        // Delete Scopes From Token
        Scope::where('integration_token_id', $tokenId)->delete();

        // Loop Scopes
        $tokenScopes = [];
        foreach($scopes as $scope) {
            $tokenScopes[] = Scope::create([
                'integration_token_id' => $tokenId,
                'scope' => $scope
            ]);
        }

        // Find Token From Relation
        return collect($tokenScopes);
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
