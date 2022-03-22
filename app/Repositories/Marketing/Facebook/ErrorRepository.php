<?php

namespace App\Repositories\Marketing\Facebook;

use App\Exceptions\NotImplementedException;
use App\Exceptions\Marketing\Facebook\NoMarketplaceErrorToDismissException;
use App\Models\Marketing\Facebook\Error;
use App\Repositories\Traits\SortTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ErrorRepository implements ErrorRepositoryInterface {
    use SortTrait;

    /**
     * Define Sort Orders
     *
     * @var array
     */
    private $sortOrders = [
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
     * Create Facebook Error
     * 
     * @param array $params
     * @return Error
     */
    public function create($params) {
        // Create Error
        return Error::create($params);
    }

    /**
     * Delete Error
     * 
     * @param int $id
     * @throws NotImplementedException
     */
    public function delete($id) {
        throw new NotImplementedException;
    }

    /**
     * Get Error
     * 
     * @param array $params
     * @return Error
     */
    public function get($params) {
        // Return Error
        return Error::findOrFail($params['id']);
    }

    /**
     * Get All Errors That Match Params
     * 
     * @param array $params
     * @return Collection<Error>
     */
    public function getAll($params) {
        $query = Error::where('marketplace_id', '=', $params['marketplace_id']);

        // Get Dismissed
        if(!isset($params['dismissed'])) {
            $params['dismissed'] = 0;
        }
        $query = $query->where('dismissed', $params['dismissed']);

        // Get Expired At Default
        if(!isset($params['expired_status'])) {
            $params['expired_status'] = Error::EXPIRED_FOLLOW;
        }

        // Include Expired Check?
        if($params['expired_status'] !== Error::EXPIRED_IGNORE) {
            $query = $query->where('expires_at', '>', DB::raw('NOW()'));
        }

        // Set Sort Query
        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->get();
    }

    /**
     * Update Error
     * 
     * @param array $params
     * @return Error
     */
    public function update($params) {
        $image = Error::findOrFail($params['id']);

        DB::transaction(function() use (&$image, $params) {
            // Fill Error Details
            $image->fill($params)->save();
        });

        return $image;
    }

    /**
     * Dismiss Error on Marketplace Integration
     * 
     * @param int $marketplaceId
     * @param int|null $errorId
     * @return Error|null
     * @throw NoMarketplaceErrorToDismissException
     */
    public function dismiss(int $marketplaceId, ?int $errorId = null): ?Error {
        // Get Single Error
        if(!empty($errorId)) {
            try {
                $error = $this->get(['id' => $errorId]);
            } catch(\Exception $e) {}
        }

        // No Error?
        if(empty($error)) {
            // Error Still Not Found?
            $errors = $this->getAll(['marketplace_id' => $marketplaceId]);

            // Get First
            if($errors->count() > 0) {
                $error = $errors->first();
            }
        }

        // Return Error
        if(!empty($error->id)) {
            return $this->update([
                'id' => $error->id,
                'dismissed' => 1
            ]);
        }

        // Throw Exception
        throw new NoMarketplaceErrorToDismissException;
    }

    /**
     * Dismiss All Errors on Marketplace Integration
     * 
     * @param int $marketplaceId
     * @return Collection<Error>
     */
    public function dismissAll(int $marketplaceId): Collection {
        // Get Errors
        $errors = $this->getAll(['marketplace_id' => $marketplaceId]);

        // Get First
        $collection = new Collection();
        foreach($errors as $error) {
            $collection->push($this->update([
                'id' => $error->id,
                'dismissed' => 1
            ]));
        }

        // Return Error
        return $collection;
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }
}
