<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Exceptions\NotImplementedException;
use App\Models\Marketing\Craigslist\Balance;
use App\Repositories\Traits\SortTrait;
use Illuminate\Support\Facades\DB;

class BalanceRepository implements BalanceRepositoryInterface {

    use SortTrait;

    /**
     * Define Sort Orders
     *
     * @var array
     */
    private $sortOrders = [
        'balance' => [
            'field' => 'balance',
            'direction' => 'DESC'
        ],
        '-balance' => [
            'field' => 'balance',
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
     * Create Balance
     * 
     * @param array $params
     * @return Balance
     */
    public function create($params) {
        // Create Balance
        return Balance::create($params);
    }

    /**
     * Delete Balance
     * 
     * @param string $code
     * @throws NotImplementedException
     */
    public function delete($code) {
        throw NotImplementedException;
    }

    /**
     * Get Balance
     * 
     * @param array $params
     * @return Balance
     */
    public function get($params) {
        // Find Balance By Dealer ID (Still Must Be Code)
        return Balance::findOrFail($params['dealer_id']);
    }

    /**
     * Get All Balance That Match Params
     * 
     * @param array $params
     * @return Collection<Balance>
     */
    public function getAll($params) {
        $query = Balance::where('dealer_id', '>', 0);

        if (isset($params['dealer_id'])) {
            $query = $query->where('dealer_id', $params['dealer_id']);
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
     * Update Balance
     * 
     * @param array $params
     * @return Balance
     */
    public function update($params) {
        $balance = $this->get($params);

        DB::transaction(function() use (&$balance, $params) {
            // Fill Balance Details
            $balance->fill($params)->save();
        });

        return $balance;
    }

    /**
     * Create OR Update Balance
     * 
     * @param array $params
     * @return Balance
     */
    public function createOrUpdate(array $params): Balance {
        // Get Balance
        $balance = $this->get($params);

        // Balance Exists? Update!
        if(!empty($balance->dealer_id)) {
            return $this->update($params);
        }

        // Create Instead
        return $this->create($params);
    }


    protected function getSortOrders() {
        return $this->sortOrders;
    }
}