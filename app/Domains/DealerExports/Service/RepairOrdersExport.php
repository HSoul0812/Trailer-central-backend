<?php

namespace App\Domains\DealerExports\Service;

use App\Contracts\DealerExports\EntityActionExportable;
use App\Domains\DealerExports\BaseExportAction;
use Illuminate\Support\Facades\DB;

/**
 * Class RepairOrdersExport
 *
 * @package App\Domains\DealerExports\Service
 */
class RepairOrdersExport extends BaseExportAction implements EntityActionExportable
{
    public const ENTITY_TYPE = 'repair_orders';

    private const APPOINTMENTS = [
        0 => 'Appointment to Wait',
        1 => 'Scheduled',
        2 => 'Walk-In',
    ];

    public function getQuery()
    {
        $groupedPayments = DB::table('qb_payment')
            ->select('repair_order_id', DB::raw('SUM(amount) as paid_amount, qb_invoices.po_no as po_no, qb_invoices.po_amount as po_amount'))
            ->leftJoin('qb_invoices', 'qb_payment.invoice_id', '=', 'qb_invoices.id')
            ->groupBy('qb_invoices.repair_order_id');

        return DB::table('dms_repair_order')
            ->select([
                'dealer_location.name as dealer_location_name',
                'dms_repair_order.*',
                'customer.id as customer_id',
                'customer.display_name',
            ])
            ->leftJoin('dealer_location as dl', 'dl.dealer_location_id', '=', 'dms_repair_order.location')
            ->leftJoin('dms_customer as customer', 'customer.id', '=', 'dms_repair_order.customer_id')
            ->leftJoinSub($groupedPayments, 'invoice', function ($join) {
                $join->on('dms_repair_order.id', '=', 'invoice.repair_order_id');
            })
            ->select('*', DB::raw('
                IF(invoice.po_no AND NOT closed_by_related_unit_sale,
                invoice.paid_amount + invoice.po_amount,
                (SELECT CASE WHEN closed_by_related_unit_sale THEN total_price ELSE invoice.paid_amount END)) AS total_paid_amount'))
            ->where('dms_repair_order.dealer_id', $this->dealer->dealer_id)
            ->orderBy('dms_repair_order.id', 'desc');
    }

    public function execute(): void
    {
        $this->setEntity(self::ENTITY_TYPE)
            ->setHeaders([
                'user_defined_id' => 'RO #',
                'customer_id' => 'Customer Identifier',
                'display_name' => 'Customer Name',
                'created_at' => 'Creation Date',
                'closed_at' => 'Completion Date',
                'total_price' => 'Total Amount',
                'total_paid_amount' => 'Received Amount',
                'status' => 'Status',
                'location' => 'Location Identifier',
                'dealer_location_name' => 'Location',
                'type' => 'Type',
                'date_in' => 'Scheduled Drop Off Date / Time',
                'date_out' => 'Scheduled  Pick Up Time',
                'appointment_name' => 'appointment',
            ])
            ->export();
    }

    public function transformRow($row)
    {
        $headers = array_keys($this->headers);

        return array_map(function (string $header) use ($row) {
            $row->appointment_name = self::APPOINTMENTS[$row->appointment] ?? null;

            return object_get($row, $header);
        }, $headers);
    }
}
