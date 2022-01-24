<?php

namespace App\Repositories\Feed;

use App\Repositories\Feed\TransactionExecuteQueueRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\Feed\TransactionExecuteQueue;
use Carbon\Carbon;

class TransactionExecuteQueueRepository implements TransactionExecuteQueueRepositoryInterface 
{
    /**
     *
     * @var TransactionExecuteQueue 
     */
    protected $model;
    
    public function __construct(TransactionExecuteQueue $transactionExecuteQueue)
    {
        $this->model = $transactionExecuteQueue;
    }
    
    /**
     * $params will contain the json data meant to be stored in the execution queue
     * 
     * @param array $params
     */
    public function create($params) 
    {
        if (!empty(TransactionExecuteQueue::SOURCE_MAPPINGS[$params['source']])) {
            $params['source'] = TransactionExecuteQueue::SOURCE_MAPPINGS[$params['source']];
        }

        $dataToInsert = [
            'queued_at' => Carbon::now()->toDateTimeString(),
            'api' => $params['source'],
            'data' => json_encode($params)
        ];
        
        return $this->model->create($dataToInsert);
    }

    public function delete($params): bool 
    {
        throw new NotImplementedException;
    }

    public function get($params) 
    {
        throw new NotImplementedException;
    }

    public function getAll($params) 
    {
        throw new NotImplementedException;
    }

    public function update($params): bool 
    {
        throw new NotImplementedException;
    }

    /**
     * {@inheritDoc}
     */
    public function createBulk(array $atwInventoryData): array 
    {
        $vinsStored = [];
        
        foreach($atwInventoryData as $atwInventory)
        {
            if ($this->create($atwInventory)) 
            {
                $vinsStored[] = $atwInventory['vin'];
            }            
        }
        
        return $vinsStored;
    }

}
