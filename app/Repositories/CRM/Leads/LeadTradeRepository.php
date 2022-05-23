<?php

namespace App\Repositories\CRM\Leads;

use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\CRM\Leads\LeadTrade;
use App\Repositories\RepositoryAbstract;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class LeadTradeRepository
 * @package App\Repositories\CRM\Leads
 */
class LeadTradeRepository extends RepositoryAbstract implements LeadTradeRepositoryInterface
{
    private const AVAILABLE_INCLUDE = [
        'images',
    ];

    /**
     * @param $params
     * @return Collection
     */
    public function getAll($params): Collection
    {
        if (empty($params['lead_id'])) {
            throw new RepositoryInvalidArgumentException('Lead id is required');
        }

        $query = LeadTrade::query();

        $query = $query->where('lead_id', '=', $params['lead_id']);

        if (isset($params['include']) && is_string($params['include'])) {
            foreach (array_intersect(self::AVAILABLE_INCLUDE, explode(',', $params['include'])) as $include) {
                $query = $query->with($include);
            }
        }

        return $query->get();
    }
}
