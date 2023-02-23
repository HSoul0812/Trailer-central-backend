<?php

namespace App\Repositories\CRM\Leads;

use App\Exceptions\CRM\Leads\MissingLeadIdGetAllTradesException;
use App\Models\CRM\Leads\LeadTrade;
use App\Repositories\RepositoryAbstract;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class LeadTradeRepository
 * 
 * @package App\Repositories\CRM\Leads
 */
class LeadTradeRepository extends RepositoryAbstract implements LeadTradeRepositoryInterface
{
    private const AVAILABLE_INCLUDE = [
        'images'
    ];


    /**
     * Create One Lead Trade
     * 
     * @param array $params
     * @return LeadTrade
     */
    public function create($params): LeadTrade {
        // Create Lead
        return LeadTrade::create($params);
    }

    /**
     * Update One Lead Trade
     * 
     * @param array $params
     * @return LeadTrade
     */
    public function update($params): LeadTrade {
        // Get Lead Trade
        $leadTrade = $this->get($params);

        // Update Lead Trade
        DB::transaction(function() use (&$leadTrade, $params) {
            $leadTrade->fill($params)->save();
        });

        // Return Full Lead Trade Details
        return $leadTrade;
    }

    /**
     * Delete One Lead Trade
     * 
     * @param array $params
     * @return bool
     */
    public function delete($params): bool {
        return LeadTrade::findOrFail($params['id'])->delete();
    }

    /**
     * Find Lead Trade By ID; don't throw error if missing
     * 
     * @param int $id
     * @return null|LeadTrade
     */
    public function find(int $id): ?LeadTrade {
        return LeadTrade::find($id);
    }

    /**
     * Get One Lead Trade
     * 
     * @param array $params
     * @return LeadTrade
     */
    public function get($params): LeadTrade {
        return LeadTrade::findOrFail($params['id']);
    }

    /**
     * Get All Lead Trades
     * 
     * @param $params
     * @return Collection<LeadTrade>
     */
    public function getAll($params): Collection
    {
        // Missing Lead ID While Getting Trades?
        if (empty($params['lead_id'])) {
            throw new MissingLeadIdGetAllTradesException;
        }

        // Initialize Lead Trade Query
        $query = LeadTrade::where('lead_id', '=', $params['lead_id']);

        // Includes?
        if (isset($params['include']) && is_string($params['include'])) {
            foreach (array_intersect(self::AVAILABLE_INCLUDE, explode(',', $params['include'])) as $include) {
                $query = $query->with($include);
            }
        }

        return $query->get();
    }
}
