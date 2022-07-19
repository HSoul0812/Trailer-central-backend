<?php


namespace App\Repositories\User;


use App\Models\User\DealerLocationMileageFee;
use App\Exceptions\NotImplementedException;
use App\Repositories\Inventory\CategoryRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DealerLocationMileageFeeRepository implements DealerLocationMileageFeeRepositoryInterface
{

    /**
     * @var DealerLocationMileageFee $locationMileageFee
     */
    private $locationMileageFee;

    /**
     * @var CategoryRepositoryInterface $inventoryCategoryRepository
     */
    private $inventoryCategoryRepository;

    public function __construct(DealerLocationMileageFee $locationMileageFee, CategoryRepositoryInterface $inventoryCategoryRepository)
    {
        $this->locationMileageFee = $locationMileageFee;
        $this->inventoryCategoryRepository = $inventoryCategoryRepository;
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
        $query = $this->locationMileageFee->query();
        if (isset($params['dealer_location_id'])) {
            $query->where('dealer_location_id', $params['dealer_location_id']);
        }
        return $query->get();
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

    /**
     * Create Mileage Fee for all Inventory Categories
     */
    public function bulkCreate($params)
    {
        //upsert is not available for Laravel 6
        $values = $this->inventoryCategoryRepository->getAll($params)->map(function ($locationMileageFee) use ($params) {
            return sprintf("(%d,%d,%d)", $params['dealer_location_id'], $locationMileageFee->inventory_category_id, $params['fee_per_mile']);
        })->join(",");
        DB::insert(sprintf('INSERT INTO dealer_location_mileage_fee (dealer_location_id, inventory_category_id, fee_per_mile) VALUES %s ON DUPLICATE KEY UPDATE fee_per_mile=%d', $values, $params['fee_per_mile']));
        return $this->getAll($params);
    }
}
