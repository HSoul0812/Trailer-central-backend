<?php

namespace App\Repositories\Website\Tracking;

use App\Exceptions\NotImplementedException;
use App\Models\Website\Tracking\TrackingUnit;
use App\Repositories\Traits\SortTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class TrackingUnitRepository
 * @package App\Repositories\Website\Tracking
 */
class TrackingUnitRepository implements TrackingUnitRepositoryInterface
{
    use SortTrait;

    private $sortOrders = [
        'date_viewed' => [
            'field' => 'date_viewed',
            'direction' => 'DESC'
        ],
        '-date_viewed' => [
            'field' => 'date_viewed',
            'direction' => 'ASC'
        ]
    ];

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function create($params)
    {
        throw new NotImplementedException;
    }

    /**
     * Update TrackingUnit 
     * 
     * @param $params
     * @return TrackingUnit
     */
    public function update($params)
    {
        $unit = TrackingUnit::findOrFail($params['id']);

        DB::transaction(function() use (&$unit, $params) {
            // Updating Tracking Details
            $unit->fill($params)->save();
        });

        return $unit;
    }

    /**
     * Get Single TrackingUnit
     * 
     * @param array $params
     * @return TrackingUnit
     */
    public function get($params)
    {
        return TrackingUnit::findOrFail($params['id']); 
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function delete($params)
    {
        throw new NotImplementedException;
    }

    /**
     * Get All TrackingUnit
     * 
     * @param array $params
     * @return Collection<TrackingUnit>
     */
    public function getAll($params): Collection
    {
        $query = TrackingUnit::where('session_id', $params['session_id']);

        if(isset($params['lead_id'])) {
            $query = $query->where('lead_id', $params['lead_id']);
        }

        if(isset($params['inventory_id'])) {
            $query = $query->where('inventory_id', $params['inventory_id']);
        }

        if(isset($params['type'])) {
            $query = $query->where('type', $params['type']);
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->get();
    }

    /**
     * Get Newest Tracking Unit
     * 
     * @param $params
     * @return TrackingUnit
     */
    public function getNewest($params): TrackingUnit
    {
        // Set Sort
        $params['sort'] = '-date_viewed';

        // Get All in Descending Order
        $units = $this->getAll($params);

        // Return First
        return $units->first();
    }


    /**
     * Mark Tracking Unit as Inquired
     * 
     * @param string $sessionId
     * @param int $unitId
     * @param string $unitType
     * @return TrackingUnit
     */
    public function markUnitInquired(string $sessionId, int $unitId, string $unitType = 'inventory'): TrackingUnit
    {
        // Fix Unit Type
        if(!in_array($unitType, TrackingUnit::VALID_UNIT_TYPES)) {
            $unitType = TrackingUnit::DEFAULT_UNIT_TYPE;
        }

        // Update Tracked Unit Details
        $unit = $this->getNewest([
            'session_id' => $sessionId,
            'inventory_id' => $unitId,
            'type' => $unitType
        ]);

        // Update Unit
        return $this->update([
            'id' => $unit->tracking_unit_id,
            'inquired' => 1
        ]);
    }
}
