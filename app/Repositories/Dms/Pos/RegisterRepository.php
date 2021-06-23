<?php

namespace App\Repositories\Dms\Pos;

use App\Models\Pos\CashMovement;
use App\Models\Pos\Outlet;
use App\Models\Pos\Register;
use App\Repositories\RepositoryAbstract;
use Illuminate\Support\Facades\DB;

class RegisterRepository extends RepositoryAbstract implements RegisterRepositoryInterface
{
    /* @var Outlet */
    private $outlet;

    public function __construct(Outlet $model)
    {
        $this->outlet = $model;
    }

    /**
     * Searches all the outlets with open registers by dealer_id
     *
     * @param int $dealerId
     * @return mixed Collection<Outlet>
     */
    public function getAllByDealerId(int $dealerId)
    {
        return $this->outlet->select('crm_pos_register.id', 'crm_pos_outlet.config', 'crm_pos_outlet.register_name')
            ->join('crm_pos_register', 'crm_pos_register.outlet_id', '=', 'crm_pos_outlet.id')
            ->whereNull('crm_pos_register.close_date')
            ->where('crm_pos_outlet.dealer_id', $dealerId)
            ->orderBy('crm_pos_outlet.register_name')
            ->get()
            ;
    }

    /**
     * Opens new register for the given outlet
     *
     * @param array $params
     * @return bool
     */
    public function open(array $params): bool
    {
        if ($this->isRegisterOpen($params['outlet_id'])) {
            return true;
        }

        try {
            DB::transaction(function () use ($params) {
                $register = new Register($params);
                $register->open_date = date('Y-m-d H:i:s');
                $register->close_date = null;
                $register->save();

                $cashMovement = new CashMovement([
                    'register_id' => $register->id,
                    'amount' => $register->floating_amount,
                    'reason' => 'Opening float',
                ]);
                $cashMovement->save();
            });

            return true;
        } catch (\Exception $exception) {
            dd($exception->getMessage());
            return false;
        }
    }

    public function isRegisterOpen(int $outletId): bool
    {
        return Register::query()->where('outlet_id', $outletId)->whereNull('close_date')->count() > 0;
    }
}
