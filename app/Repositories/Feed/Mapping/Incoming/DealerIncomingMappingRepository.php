<?php

namespace App\Repositories\Feed\Mapping\Incoming;

use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\Feed\Mapping\Incoming\DealerIncomingMapping;
use App\Repositories\RepositoryAbstract;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class DealerIncomingMappingRepository
 * @package App\Repositories\Feed\Mapping\Incoming
 */
class DealerIncomingMappingRepository extends RepositoryAbstract implements DealerIncomingMappingRepositoryInterface
{
    /**
     * @param $params
     * @return Collection
     */
    public function getAll($params): Collection
    {
        if (empty($params['dealer_id']) && empty($params['integration_name'])) {
            throw new RepositoryInvalidArgumentException('dealer_id or integration_name has been missed. Params - ' . json_encode($params));
        }

        $query = DealerIncomingMapping::query();

        if (!empty($params['dealer_id'])) {
            $query->where(['dealer_id' => $params['dealer_id']]);
        }

        if (!empty($params['integration_name'])) {
            $query->where(['integration_name' => $params['integration_name']]);
        }

        return $query->get();
    }
}
