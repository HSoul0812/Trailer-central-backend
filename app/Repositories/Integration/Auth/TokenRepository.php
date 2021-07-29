<?php

namespace App\Repositories\Integration\Auth;

use App\Exceptions\NotImplementedException;
use App\Models\Integration\Auth\AccessToken;
use App\Models\Integration\Auth\Scope;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Carbon\CarbonImmutable;

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
        return $this->get(['id' => $accessToken->id]);
    }

    /**
     * Delete Access Token
     * 
     * @param boolean
     */
    public function delete($params) {
        // Find Token
        $token = $this->find($params);

        // Token Exists?
        if(!empty($token->id)) {
            return $token->delete();
        }

        // Already Deleted
        return true;
    }

    /**
     * Delete All Access Token
     * 
     * @param string $type
     * @param int $id
     * @return int
     */
    public function deleteAll(string $type, int $id): int
    {
        // Get Relations to Delete
        $relations = $this->getRelations($type, $id);

        // Loop Relations to Delete
        $deleted = 0;
        foreach($relations as $relation) {
            if($this->delete(['id' => $relation->id])) {
                $deleted++;
            }
        }

        // Return Count of Deleted Successfully
        return $deleted;
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
     * @return null|AccessToken
     */
    public function getRelation(array $params): ?AccessToken {
        // Find Token From Relation
        $token = AccessToken::where('relation_type', $params['relation_type'])
                            ->where('relation_id', $params['relation_id']);

        // Token Type Exists?
        if(!empty($params['token_type'])) {
            $token = $token->where('token_type', $params['token_type']);
        }
        // Find MOST RECENT Token Instead!
        else {
            $token = $this->addSortQuery($token, 'issued_at');
        }

        // Return First
        return $token->first();
    }

    /**
     * Get Access Tokens Via Relation
     * 
     * @param string $type
     * @param int $id
     * @return Collection<AccessToken>
     */
    public function getRelations(string $type, int $id): Collection {
        // Find Token From Relation
        return AccessToken::where('relation_type', $type)
                          ->where('relation_id', $id)
                          ->get();
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
        if (isset($params['relation_type']) && isset($params['relation_id'])) {
            // Find Token From Relation
            $token = AccessToken::where('relation_type', $params['relation_type'])
                                ->where('relation_id', $params['relation_id']);

            // Token Type Exists?
            if(!empty($params['token_type'])) {
                $token = $token->where('token_type', $params['token_type']);
            }
            // Find MOST RECENT Token Instead!
            else {
                $token = $this->addSortQuery($token, 'issued_at');
            }

            // Get Access Token
            $accessToken = $token->first();

            // Return Access Token
            if(!empty($accessToken->id)) {
                return $accessToken;
            }
        }

        // Return Empty
        return null;
    }

    /**
     * Get By Token
     * 
     * @param string $accessToken
     * @return null|AccessToken
     */
    public function getByToken(string $accessToken): ?AccessToken {
        // Get Access Token Entry By Actual Token
        return AccessToken::where('access_token', $accessToken)->first();
    }

    /**
     * Refresh Access Token
     * 
     * @param int $tokenId
     * @param array $newToken
     * @return AccessToken
     */
    public function refresh($tokenId, $newToken) {
        // Refresh Access Token
        $time = CarbonImmutable::now();
        return $this->update([
            'id' => $tokenId,
            'access_token' => $newToken['access_token'],
            'refresh_token' => $newToken['refresh_token'],
            'id_token' => $newToken['id_token'],
            'expires_in' => $newToken['expires_in'],
            'expires_at' => $time->addSeconds($newToken['expires_in'])->toDateTimeString(),
            'issued_at' => $time->toDateTimeString()
        ]);
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
