<?php


namespace App\Repositories\Dms\ServiceOrder;


use App\Models\CRM\Dms\ServiceOrder\ServiceItemTechnician;
use App\Repositories\RepositoryAbstract;
use App\Utilities\JsonApi\WithRequestQueryable;
use Illuminate\Database\Eloquent\Builder;

class ServiceItemTechnicianRepository extends RepositoryAbstract implements ServiceItemTechnicianRepositoryInterface
{
    use WithRequestQueryable;

    public function __construct(Builder $baseQuery)
    {
        $this->withQuery($baseQuery);
    }

    public function get($params)
    {
        return $this->query()->get();
    }

    public function findByLocation($locationId)
    {
        return $this->query()
            ->with(['serviceItem', 'serviceItem.serviceOrder'])
            ->whereHas('serviceItem.serviceOrder', function($query) use ($locationId) {
                $query->where('location', '=', $locationId);
            })
            ->get();
    }
}
