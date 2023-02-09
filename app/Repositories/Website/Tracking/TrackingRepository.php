<?php

namespace App\Repositories\Website\Tracking;

use App\Exceptions\NotImplementedException;
use App\Models\Website\Tracking\Tracking;
use App\Repositories\Traits\SortTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Class TrackingRepository
 * @package App\Repositories\Website\Tracking
 */
class TrackingRepository implements TrackingRepositoryInterface
{
    use SortTrait;

    private $sortOrders = [
        'date_created' => [
            'field' => 'date_created',
            'direction' => 'DESC'
        ],
        '-date_created' => [
            'field' => 'date_created',
            'direction' => 'ASC'
        ],
        'date_inquired' => [
            'field' => 'date_inquired',
            'direction' => 'DESC'
        ],
        '-date_inquired' => [
            'field' => 'date_inquired',
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
     * Update Tracking
     * 
     * @param array $params
     * @return Tracking
     */
    public function update($params)
    {
        $tracking = $this->find($params);

        DB::transaction(function() use (&$tracking, $params) {
            // Updating Tracking Details
            if ($tracking) {
                $tracking->fill($params)->save();
            }
        });

        return $tracking;
    }

    /**
     * Get Single Tracking
     * 
     * @param array $params
     * @return Tracking
     */
    public function get($params)
    {
        return Tracking::findOrFail($params['id']); 
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
     * @param $params
     * @throws NotImplementedException
     */
    public function getAll($params): Collection
    {
        throw new NotImplementedException;
    }


    /**
     * Find Tracking Data From Params
     * 
     * @param array $params
     * @return null|Tracking
     */
    public function find(array $params): ?Tracking
    {
        // Find By Session ID
        if(!empty($params['session_id'])) {
            return Tracking::where('session_id', $params['session_id'])->first();
        }

        // Find By ID
        return Tracking::find($params['id']);
    }

    /**
     * Update Lead on Tracking
     * 
     * @param array $params
     * @return Tracking
     */
    public function updateTrackLead(string $sessionId, int $leadId): ?Tracking
    {
        // Update Lead on Tracking
        return $this->update([
            'session_id' => $sessionId,
            'lead_id' => $leadId,
            'date_inquired' => Carbon::now()->setTimezone('UTC')->toDateTimeString()
        ]);
    }

    /**
     * @param array $data Data to replace
     * @param array $where Condition
     * @return int Total data affected
     */
    public function batchUpdate(array $data, array $where)
    {
        return Tracking::where($where)->update($data);
    }
}