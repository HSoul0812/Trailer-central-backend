<?php
namespace App\Repositories\Ecommerce;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Traits\Repository\Transaction;

class CompletedOrderRepository implements CompletedOrderRepositoryInterface
{
    use Transaction;

    public function getAll($params)
    {
        return CompletedOrder::all();
    }

    public function create($params): CompletedOrder
    {
        $completedOrder = new CompletedOrder();

        $data = $params['data']['object'];

        $completedOrder->customer_email = $data['customer_details']['email'];
        $completedOrder->total_amount = $data['amount_total'];
        $completedOrder->payment_method = $data['payment_method_types'][0];
        $completedOrder->payment_status = $data['payment_status'];
        $completedOrder->event_id = $params['id'];
        $completedOrder->object_id = $data['id'];
        $completedOrder->stripe_customer = $data['customer'];

        $completedOrder->save();

        return $completedOrder;
    }

    public function update($params)
    {
        // TODO: Implement delete() method.
    }

    public function get($params)
    {
        if (isset($params['id'])) {
            return CompletedOrder::findOrFail($params['id']);
        }
    }

    public function delete($params)
    {
        // TODO: Implement delete() method.
    }
}