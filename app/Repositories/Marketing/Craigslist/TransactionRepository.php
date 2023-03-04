<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Exceptions\NotImplementedException;
use App\Models\Marketing\Craigslist\Transaction;
use App\Repositories\Traits\SortTrait;
use Illuminate\Support\Facades\DB;

class TransactionRepository implements TransactionRepositoryInterface {

    use SortTrait;

    /**
     * Define Sort Orders
     *
     * @var array
     */
    private $sortOrders = [
        'id' => [
            'field' => 'clapp_txn_id',
            'direction' => 'DESC'
        ],
        '-id' => [
            'field' => 'clapp_txn_id',
            'direction' => 'ASC'
        ],
        'balance' => [
            'field' => 'balance',
            'direction' => 'DESC'
        ],
        '-balance' => [
            'field' => 'balance',
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
     * Create Transaction
     * 
     * @param array $params
     * @return Transaction
     */
    public function create($params) {
        // Create Transaction
        return Transaction::create($params);
    }

    /**
     * Delete Transaction
     * 
     * @param string $code
     * @throws NotImplementedException
     */
    public function delete($code) {
        throw NotImplementedException;
    }

    /**
     * Get Transaction
     * 
     * @param array $params
     * @return Transaction
     */
    public function get($params) {
        // Find Transaction By ID
        return Transaction::findOrFail($params['id']);
    }

    /**
     * Get All Transaction That Match Params
     * 
     * @param array $params
     * @return Collection<Transaction>
     */
    public function getAll($params) {
        $query = Transaction::where('clapp_txn_id', '>', 0);

        if (isset($params['dealer_id'])) {
            $query = $query->where('dealer_id', $params['dealer_id']);
        }

        if (isset($params['session_id'])) {
            $query = $query->where('session_id', $params['session_id']);
        }

        if (isset($params['queue_id'])) {
            $query = $query->where('queue_id', $params['queue_id']);
        }

        if (isset($params['inventory_id'])) {
            $query = $query->where('inventory_id', $params['inventory_id']);
        }

        if (!isset($params['per_page'])) {
            $params['per_page'] = 1000;
        }

        if(!isset($params['sort'])) {
            $params['sort'] = '-id';
        }
        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    /**
     * Update Transaction
     * 
     * @param array $params
     * @return Transaction
     */
    public function update($params) {
        $transaction = $this->get($params);

        DB::transaction(function() use (&$transaction, $params) {
            // Fill Transaction Details
            $transaction->fill($params)->save();
        });

        return $transaction;
    }

    /**
     * Create OR Update Transaction
     * 
     * @param array $params
     * @return Transaction
     */
    public function createOrUpdate(array $params): Transaction {
        // Get Transaction
        $transaction = $this->get($params);

        // Transaction Exists? Update!
        if(!empty($transaction->dealer_id)) {
            return $this->update($params);
        }

        // Create Instead
        return $this->create($params);
    }


    protected function getSortOrders() {
        return $this->sortOrders;
    }
}