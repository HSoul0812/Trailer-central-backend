<?php

namespace App\Repositories\Dms\Pos;

use App\Models\Pos\Outlet;
use App\Repositories\RepositoryAbstract;

class RegisterRepository extends RepositoryAbstract implements RegisterRepositoryInterface
{
    /**
     * Searches all the outlets with open registers by dealer_id
     *
     * @param int $dealerId
     * @return mixed array<Outlet>
     */
    public function getAllByDealerId(int $dealerId)
    {
        return Outlet::select('crm_pos_register.id', 'crm_pos_outlet.config', 'crm_pos_outlet.register_name')
            ->join('crm_pos_register', 'crm_pos_register.outlet_id', '=', 'crm_pos_outlet.id')
            ->whereNull('crm_pos_register.close_date')
            ->where('crm_pos_outlet.dealer_id', $dealerId)
            ->orderBy('crm_pos_outlet.register_name')
            ->get()
            ;
    }
}
