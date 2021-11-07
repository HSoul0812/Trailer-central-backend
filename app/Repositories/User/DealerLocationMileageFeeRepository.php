<?php


namespace App\Repositories\User;


use App\Models\User\DealerLocationMileageFee;
use App\Exceptions\NotImplementedException;

class DealerLocationMileageFeeRepository implements DealerLocationMileageFeeRepositoryInterface
{

    /**
     * @var DealerLocationMileageFee $locationMileageFee
     */
    private $locationMileageFee;

    public function __construct(DealerLocationMileageFee $locationMileageFee) {
        $this->locationMileageFee = $locationMileageFee;
    }

    /**
     * @param $params
     * @return DealerLocationMileageFee
     */
    public function create($params): DealerLocationMileageFee
    {
        $uniqueKeys = ['dealer_location_id', 'inventory_category_id'];
        return $this->locationMileageFee->updateOrCreate(
            array_intersect_key($params, array_flip($uniqueKeys)),
            array_diff_key($params, array_flip($uniqueKeys))
        );
    }

    /**
     * @param array $params
     * @return bool|void
     */
    public function delete($params)
    {
        $this->locationMileageFee->where('id', $params['id'])->delete();
    }

    /**
     * @param array $params
     */
    public function getAll($params)
    {
        throw new NotImplementedException();
    }

    /**
     * @param array $params
     * @return mixed|void
     */
    public function get($params)
    {
        return $this->locationMileageFee
            ->where('inventory_category_id', $params['inventory_category_id'])
            ->where('dealer_location_id', $params['dealer_location_id'])
            ->firstOrFail();
    }

    /**
     * @param array $params
     * @return bool|void
     */
    public function update($params)
    {
        throw new NotImplementedException();
    }
}
