<?php


namespace App\Transformers\Reports\SalesPerson;


use League\Fractal\TransformerAbstract;

class SalesReportTransformer extends TransformerAbstract
{
    public function transform($params): array
    {
        $result = [];
        foreach ($params as $salesPersonId => $salesPersonData) {
            foreach ($salesPersonData as $row) {
                $result[$salesPersonId][] = [
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'sale_id' => $row['sale_id'],
                    'invoice_id' => $row['invoice_id'],
                    'invoice_doc_num' => $row['doc_num'],
                    'sale_type' => $row['sale_type'],
                    'sale_date' => date('Y-m-d', strtotime($row['sale_date'])),
                    'sales_person_id' => (int)$row['sales_person_id'],
                    'customer_name' => $row['customer_name'],
                    'unit_sale_amount' => (float)$row['unit_sale_amount'],
                    'unit_cost_amount' => (float)$row['unit_cost_amount'],
                    'retail_price' => (float)$row['retail_price'],
                    'retail_discount' => (float)$row['retail_discount'],
                    'cost_overhead' => (float)$row['cost_overhead'],
                    'true_total_cost' => (float)$row['true_total_cost'],
                    'inventory_stock' => $row['inventory_stock'],
                    'inventory_make' => $row['inventory_make'],
                    'inventory_notes' => $row['inventory_notes']
                ];
            }
        }
        return $result;
    }
}
