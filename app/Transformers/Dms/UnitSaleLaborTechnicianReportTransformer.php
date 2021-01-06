<?php

namespace App\Transformers\Dms;

use League\Fractal\TransformerAbstract;

/**
 * Class UnitSaleLaborTechnicianReportTransformer
 * @package App\Transformers\Dms
 */
class UnitSaleLaborTechnicianReportTransformer extends TransformerAbstract
{
    public function transform($params)
    {
        $result = [];

        foreach ($params as $salesPersonId => $salesPersonData) {
            foreach ($salesPersonData as $row) {
                $result[$salesPersonId][] = [
                    'technician' => $row['technician'],
                    'type' => 'Misc Labor',
                    'act_hrs' => $row['actual_hours'],
                    'paid_hrs' => $row['paid_hours'],
                    'billed_hrs' => $row['billed_hours'],
                    'sale_id' => $row['sale_id'],
                    'invoice_id' => $row['invoice_id'],
                    'invoice_total' => (float)$row['invoice_total'],
                    'invoice_doc_num' => $row['doc_num'],
                    'sale_date' => $row['sale_date'],
                    'sales_person_id' => (int)$row['sales_person_id'],
                    'customer_name' => $row['customer_name'],
                    'unit_sale_amount' => (float)$row['unit_sale_amount'],
                    'unit_cost_amount' => (float)$row['unit_cost_amount'],
                    'part_sale_amount' => (float)$row['part_sale_amount'],
                    'part_cost_amount' => (float)$row['part_cost_amount'],
                    'labor_sale_amount' => (float)$row['labor_sale_amount'],
                    'labor_cost_amount' => (float)$row['labor_cost_amount'],
                    'inventory_stock' => $row['inventory_stock'],
                    'inventory_make' => $row['inventory_make'],
                    'inventory_notes' => $row['inventory_notes'],
                ];
            }
        }

        return $result;
    }
}
