<?php
namespace App\Repositories\Dms;


use App\Models\CRM\Account\Invoice;
use App\Models\CRM\Account\Payment;
use App\Models\CRM\Dms\Payment\DealerRefund;
use App\Models\CRM\Dms\UnitSale;
use Illuminate\Support\Facades\DB;

class UnitSaleRepository implements UnitSaleRepositoryInterface
{

    public function create($params)
    {
        // TODO: Implement create() method.
    }

    public function update($params)
    {
        // TODO: Implement update() method.
    }

    public function get($params)
    {
        // TODO: Implement get() method.
    }

    public function delete($params)
    {
        // TODO: Implement delete() method.
    }

    public function getAll($params)
    {
        // TODO: Implement getAll() method.
    }

    /**
     * Return total amount of received for the unit sale
     * Return 0 if there are no payments for the unit sale
     * @param $unitSaleId
     * @param array $params
     * @return float
     */
    public function getTotalReceived($unitSaleId, $params = []): float
    {
        $dealerRefunds = DealerRefund::getTableName();
        $invoice = Invoice::getTableName();

        $query = DB::table("qb_payment as p")->select(DB::raw('p.amount - COALESCE(SUM(refund.amount), 0) AS amount_received'))
            ->join($dealerRefunds . ' AS refund', 'p.id' , '=', 'refund.tb_primary_id')
            ->join($invoice. ' AS i', 'i.id', '=', 'p.invoice_id')
            ->where('i.unit_sale_id', '=', $unitSaleId)
            ->where('refund.tb_name', '=', 'qb_payment');

        if (isset($params['isIncomeDownPayment'])) {
            $query = $query->where('p.income_down_payment', '=', $params['isIncomeDownPayment']);
        }

        $payments = $query->groupBy('p.id')->get();

        $totalReceived = 0;
        foreach ($payments as $payment) {
            $totalReceived += $payment['amount_received'];
        }

        return $totalReceived;
    }
}