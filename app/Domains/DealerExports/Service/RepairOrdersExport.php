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

    public function getQuery()
    {
        $groupedPayments = DB::table('qb_payment')
            ->select('repair_order_id', DB::raw('SUM(amount) as paid_amount, qb_invoices.po_no as po_no, qb_invoices.po_amount as po_amount'))
            ->leftJoin('qb_invoices', 'qb_payment.invoice_id', '=', 'qb_invoices.id')
            ->groupBy('qb_invoices.repair_order_id');

        return DB::table('dms_repair_order')
            ->select([
                'dms_repair_order.*',
                'customer.id as customer_id',
                'customer.display_name',
                DB::raw(
                    "case
                        when dms_repair_order.appointment = 0 then 'Appointment to Wait'
                        when dms_repair_order.appointment = 1 then 'Scheduled'
                        when dms_repair_order.appointment = 2 then 'Walk-In'
                    end as appointment"
                ),
                DB::raw('
                    IF(invoice.po_no AND NOT closed_by_related_unit_sale,
                    invoice.paid_amount + invoice.po_amount,
                    (SELECT CASE WHEN closed_by_related_unit_sale THEN total_price ELSE invoice.paid_amount END)) AS total_paid_amount'
                ),
                DB::raw('dl.name as dealer_location_name'),
                DB::raw('dms_service_item.repair_no as repair_order_item_number'),
                DB::raw('dms_service_item.problem as repair_order_item_problem'),
                DB::raw('dms_service_item.cause as repair_order_item_cause'),
                DB::raw('dms_service_item.solution as repair_order_item_solution'),
                DB::raw('dms_service_item.job_status as repair_order_item_status'),
                DB::raw('dms_service_item.amount as repair_order_item_amount'),
                DB::raw('dms_service_item.notes as repair_order_item_notes'),
                DB::raw('dms_service_item.quantity as repair_order_item_quantity'),
                DB::raw('dms_service_item.taxable as repair_order_item_taxable'),
                DB::raw('dms_service_item.claim_no as repair_order_item_claim_number'),
                DB::raw('dms_settings_technician.id as repair_order_item_technician_id'),
                DB::raw('dms_settings_technician.email as repair_order_item_technician_email'),
                DB::raw('dms_settings_technician.hourly_rate as repair_order_item_technician_hourly_rate'),
                DB::raw('dms_service_technician.act_hrs as repair_order_item_technician_actual_hours'),
                DB::raw('dms_service_technician.paid_hrs as repair_order_item_technician_paid_hours'),
                DB::raw('dms_service_technician.billed_hrs as repair_order_item_technician_billed_hours'),
                DB::raw('dms_service_technician.discount as repair_order_item_technician_discount'),
                DB::raw('dms_service_technician.is_completed as repair_order_item_technician_completed'),
                DB::raw('dms_service_technician.start_date as repair_order_item_technician_start_date'),
                DB::raw('dms_service_technician.completed_date as repair_order_item_technician_completed_date'),
                DB::raw('dms_service_technician.miles_in as repair_order_item_technician_miles_in'),
                DB::raw('dms_service_technician.miles_out as repair_order_item_technician_miles_out'),
                DB::raw('dms_part_item.part_id as repair_order_part_identifier'),
                DB::raw('parts.title as repair_order_part_title'),
                DB::raw('parts.sku as repair_order_part_sku'),
                DB::raw('parts.description as repair_order_part_description'),
                DB::raw('vendors.id as repair_order_part_vendor_id'),
                DB::raw('vendors.name as repair_order_part_vendor_name'),
                DB::raw('dms_part_item.bin_id as repair_order_part_bin_id'),
                DB::raw('bins.bin_name as repair_order_part_bin_name'),
                DB::raw('dms_part_item.qty as repair_order_part_quantity'),
                DB::raw('dms_part_item.price as repair_order_part_price'),
                DB::raw('dms_part_item.notes as repair_order_part_notes'),
                DB::raw('dms_part_item.taxable as repair_order_part_taxable'),
                DB::raw('misc_parts.title as repair_order_misc_part_title'),
                DB::raw('misc_parts.dealer_cost as repair_order_misc_part_dealer_cost'),
                DB::raw('misc_parts.unit_price as repair_order_misc_part_unit_price'),
                DB::raw('misc_parts.quantity as repair_order_misc_part_quantity'),
                DB::raw('misc_parts.notes as repair_order_misc_part_notes'),
                DB::raw('misc_parts.taxable as repair_order_misc_part_taxable'),
                DB::raw('other_items.id as repair_order_other_items_id'),
                DB::raw('other_items_vendors.id as repair_order_other_items_vendor_id'),
                DB::raw('other_items_vendors.name as repair_order_other_items_vendor_name'),
                DB::raw('other_items.type as repair_order_other_items_type'),
                DB::raw('other_items.description as repair_order_other_items_description'),
                DB::raw('other_items.cost as repair_order_other_items_cost'),
                DB::raw('other_items.amount as repair_order_other_items_amount'),
                DB::raw('other_items.notes as repair_order_other_items_notes'),
                DB::raw('other_items.taxable as repair_order_other_items_taxable'),
                DB::raw('other_items.is_custom_amount as repair_order_other_items_is_custom_amount'),
            ])
            ->leftJoin('dealer_location as dl', 'dl.dealer_location_id', '=', 'dms_repair_order.location')
            ->leftJoin('dms_customer as customer', 'customer.id', '=', 'dms_repair_order.customer_id')
            ->leftJoin('dms_service_item', 'dms_service_item.repair_order_id', '=', 'dms_repair_order.id')
            ->leftJoin('dms_service_technician', 'dms_service_technician.service_item_id', '=', 'dms_service_item.id')
            ->leftJoin('dms_settings_technician', 'dms_settings_technician.id', '=', 'dms_service_technician.dms_settings_technician_id')
            ->leftJoin('dms_part_item', 'dms_part_item.repair_order_id', '=', 'dms_repair_order.id')
            ->leftJoin('parts_v1 as parts', 'parts.id', '=', 'dms_part_item.part_id')
            ->leftJoin('qb_vendors as vendors', 'vendors.id', '=', 'parts.vendor_id')
            ->leftJoin('dms_settings_part_bin as bins', 'bins.id', '=', 'dms_part_item.bin_id')
            ->leftJoin('dms_repair_misc_part_item as misc_parts', 'misc_parts.id', '=', 'dms_repair_order.id')
            ->leftJoin('dms_other_item as other_items', 'other_items.repair_order_id', '=', 'dms_repair_order.id')
            ->leftJoin('qb_vendors as other_items_vendors', 'other_items_vendors.id', '=', 'other_items.vendor_id')
            ->leftJoinSub($groupedPayments, 'invoice', function ($join) {
                $join->on('dms_repair_order.id', '=', 'invoice.repair_order_id');
            })
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
                'date_in' => 'Scheduled Drop Off',
                'date_out' => 'Scheduled Pick Up',
                'notified_by' => 'Notified By',
                'notified_at' => 'Notified At',
                'public_memo' => 'Public Memo',
                'provate_memo' => 'Private Memo',
                'total_price' => 'Total Amount',
                'total_paid_amount' => 'Received Amount',
                'status' => 'Status',
                'key_tag' => 'Key Tag #',
                'location' => 'Location Identifier',
                'dealer_location_name' => 'Location',
                'type' => 'Type',
                'appointment' => 'Appointment',
                'repair_order_item_number' => 'Repair Item #',
                'repair_order_item_claim_number' => 'Repair Item Claim #',
                'repair_order_item_problem' => 'Repair Item Problem',
                'repair_order_item_cause' => 'Repair Item Cause',
                'repair_order_item_solution' => 'Repair Item Solution',
                'repair_order_item_status' => 'Repair Item Status',
                'repair_order_item_amount' => 'Repair Item Amount',
                'repair_order_item_notes' => 'Repair Item Notes',
                'repair_order_item_quantity' => 'Repair Item Quantity',
                'repair_order_item_taxable' => 'Repair Item Taxable',
                'repair_order_item_technician_id' => 'Repair Item Technician Identifier',
                'repair_order_item_technician_email' => 'Repair Item Technician Email',
                'repair_order_item_technician_hourly_rate' => 'Repair Item Technician Hourly Rate',
                'repair_order_item_technician_actual_hours' => 'Repair Item Technician Actual Hours',
                'repair_order_item_technician_paid_hours' => 'Repair Item Technician Paid Hours',
                'repair_order_item_technician_billed_hours' => 'Repair Item Technician Billed Hours',
                'repair_order_item_technician_discount' => 'Repair Item Technician Discount',
                'repair_order_item_technician_completed' => 'Repair Item Technician Is Completed',
                'repair_order_item_technician_start_date' => 'Repair Item Technician Start Date',
                'repair_order_item_technician_completed_date' => 'Repair Item Technician Completed Date',
                'repair_order_item_technician_miles_in' => 'Repair Item Technician Miles In',
                'repair_order_item_technician_miles_out' => 'Repair Item Technician Miles Out',
                'labor_discount' => 'Labor Discount',
                'part_discount' => 'Part Discount',
                'repair_order_part_identifier' => 'Repair Order Part Identifier',
                'repair_order_part_title' => 'Repair Order Part Title',
                'repair_order_part_sku' => 'Repair Order Part SKU',
                'repair_order_part_description' => 'Repair Order Part Description',
                'repair_order_part_price' => 'Repair Order Part Price',
                'repair_order_part_vendor_id' => 'Repair Order Part Vendor Identifier',
                'repair_order_part_vendor_name' => 'Repair Order Part Vendor Name',
                'repair_order_part_bin_id' => 'Repair Order Part Bin Identifier',
                'repair_order_part_bin_name' => 'Repair Order Part Bin Name',
                'repair_order_part_quantity' => 'Repair Order Part Qty',
                'repair_order_part_taxable' => 'Repair Order Part Is Taxable',
                'repair_order_part_notes' => 'Repair Order Part Notes',
                'repair_order_misc_part_title' => 'Repair Order Misc Part Title',
                'repair_order_misc_part_dealer_cost' => 'Repair Order Misc Part Cost',
                'repair_order_misc_part_unit_price' => 'Repair Order Misc Part Price',
                'repair_order_misc_part_quantity' => 'Repair Order Misc Part Qty',
                'repair_order_misc_part_notes' => 'Repair Order Misc Part Notes',
                'repair_order_misc_part_taxable' => 'Repair Order Misc Part Taxable',
                'repair_order_other_items_id' => 'Repair Order Other Items Identifier',
                'repair_order_other_items_vendor_id' => 'Repair Order Other Items Vendor Identifier',
                'repair_order_other_items_vendor_name' => 'Repair Order Other Items Vendor Name',
                'repair_order_other_items_type' => 'Repair Order Other Items Type',
                'repair_order_other_items_cost' => 'Repair Order Other Items Cost',
                'repair_order_other_items_amount' => 'Repair Order Other Items Amount',
                'repair_order_other_items_notes' => 'Repair Order Other Items Notes',
                'repair_order_other_items_taxable' => 'Repair Order Other Items Taxable',
                'repair_order_other_items_is_custom_amount' => 'Repair Order Other Items Is Custom Amount',
            ])
            ->export();
    }
}
