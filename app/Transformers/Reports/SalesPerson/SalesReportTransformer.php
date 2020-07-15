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
                    'sale_type' => $row['sale_type'],
                    'sale_date' => $row['sale_date'],
                    'sales_person_id' => (int)$row['sales_person_id'],
                    'customer_name' => $row['customer_name'],
                    'sale_amount' => (float)$row['sale_amount'],
                    'cost_amount' => (float)$row['cost_amount'],
                ];
            }
        }
        return $result;
    }
}
