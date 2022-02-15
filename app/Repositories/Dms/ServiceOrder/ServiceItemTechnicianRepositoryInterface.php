<?php


namespace App\Repositories\Dms\ServiceOrder;


use App\Models\CRM\Dms\ServiceOrder\ServiceItemTechnician;
use App\Repositories\Repository;
use App\Utilities\JsonApi\RequestQueryable;
use Illuminate\Database\Eloquent\Collection;

interface ServiceItemTechnicianRepositoryInterface extends Repository, RequestQueryable
{

    /**
     * Find all technicians scheduled by dealer location
     * @param $locationId
     * @return Collection<ServiceItemTechnician>
     */
    public function findByLocation($locationId);

    /**
     * @param $params
     * @return Collection
     */
    public function serviceReport($params): array;
}
