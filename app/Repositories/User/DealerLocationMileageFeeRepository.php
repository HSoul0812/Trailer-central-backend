<?php


namespace App\Repositories\User;


use App\DealerLocationMileageFee;
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
        return $this->locationMileageFee->create(
            $params
        );
    }

    /**
     * @param array $params
     * @return DealerLocationMileageFee
     */
    public function update($params): DealerLocationMileageFee
    {
        $mileageFee = $this->locationMileageFee->findOrFail($params['id']);
        $mileageFee->fill($params)->save();
        return $mileageFee;
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
}
