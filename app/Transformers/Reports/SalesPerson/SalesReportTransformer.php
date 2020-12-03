<?php


namespace App\Transformers\Reports\SalesPerson;


use League\Fractal\TransformerAbstract;

class SalesReportTransformer extends TransformerAbstract
{
    public function transform($params) {
        $result = [];
        foreach ($params as $salesPersonId => $salesPersonData) {
            foreach ($salesPersonData as $row) {
                $result[$salesPersonId][] = [
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'sale_id' => $row['sale_id'],
                    'invoice_id' => $row['invoice_id'],
                    'invoice_total' => (float)$row['invoice_total'],
                    'invoice_doc_num' => $row['doc_num'],
                    'sale_type' => $row['sale_type'],
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
                    'inventory_notes' => $row['inventory_notes']
                ];
            }
        }
        return $result;
    }
}
