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
        if (isset($params['without_prepare_data']) && $params['without_prepare_data']) {
            unset($params['without_prepare_data']);
            $dataToInsert = $params;
        } else {
            $dataToInsert = $this->prepareInventoryDataForInsert($params, $params['is_update']);
        }

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
            $atwInventory['is_update'] = false;
            if ($this->create($atwInventory))
            {
                $vinsStored[] = $atwInventory['vin'];
            }
        }

        return $vinsStored;
    }

    /**
     * {@inheritDoc}
     */
    public function updateBulk(array $atwInventoryData): array
    {
        $vinsStored = [];

        foreach($atwInventoryData as $atwInventory)
        {
            $atwInventory['is_update'] = true;
            if ($this->create($atwInventory))
            {
                $vinsStored[] = $atwInventory['vin'];
            }
        }

        return $vinsStored;
    }

    /**
     * Prepares the ATW inventory data to be inserted in the database
     *
     * @param array $data
     * @param bool $isUpdate
     * @return array
     */
    private function prepareInventoryDataForInsert(array $data, bool $isUpdate = false): array
    {
        if (!empty(TransactionExecuteQueue::SOURCE_MAPPINGS[$data['source']])) {
            $data['source'] = TransactionExecuteQueue::SOURCE_MAPPINGS[$data['source']];
        }

        $dataToInsert = [
            'queued_at' => Carbon::now()->toDateTimeString(),
            'api' => $data['source'],
            'data' => $data,
            'operation_type' => $isUpdate ? TransactionExecuteQueue::UPDATE_OPERATION_TYPE : TransactionExecuteQueue::INSERT_OPERATION_TYPE
        ];

        return $dataToInsert;
    }

}
