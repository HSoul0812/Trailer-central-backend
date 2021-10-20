<?php
namespace App\Repositories\Ecommerce;

use App\Events\Ecommerce\QtyUpdated;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Traits\Repository\Transaction;

class CompletedOrderRepository implements CompletedOrderRepositoryInterface
{
    use Transaction;

    public function getAll($params)
    {
      if (!isset($params['per_page'])) {
          $params['per_page'] = 100;
      }

      $query = CompletedOrder::select('*');

      if (isset($params['status'])) {
          $query->where('status', $params['status']);
      }


      return $query->paginate($params['per_page'])->appends($params);
    }

    public function create($params): CompletedOrder
    {
        $data = $params['data']['object'];

        $completedOrder = CompletedOrder::where('object_id', $data['id'])->first();

        if (!$completedOrder) {
            $completedOrder = new CompletedOrder();

            $completedOrder->event_id = $params['id'];
            $completedOrder->object_id = $data['id'];
            $completedOrder->parts = isset($data['parts']) ? json_decode($data['parts'], true) : [];
            $completedOrder->total_amount = $data['amount_total'] / 100; // Since Stripe use the amount in cents, we need to convert it
            $completedOrder->payment_status = $data['payment_status'] ?? '';
            $completedOrder->payment_intent = $data['payment_intent'] ?? null;

            $completedOrder->shipping_name = $data['shipto_name'] ?? '';
            $completedOrder->shipping_country = $data['shipto_country'] ?? '';
            $completedOrder->shipping_address = $data['shipto_address'] ?? '';
            $completedOrder->shipping_city = $data['shipto_city'] ?? '';
            $completedOrder->shipping_zip = $data['shipto_postal'] ?? '';
            $completedOrder->shipping_region = $data['shipto_region'] ?? '';

            if (isset($data['no-billing']) && $data['no-billing'] == "1") {

                $completedOrder->billing_name = $data['shipto_name'] ?? '';
                $completedOrder->billing_country = $data['shipto_country'] ?? '';
                $completedOrder->billing_address = $data['shipto_address'] ?? '';
                $completedOrder->billing_city = $data['shipto_city'] ?? '';
                $completedOrder->billing_zip = $data['shipto_postal'] ?? '';
                $completedOrder->billing_region = $data['shipto_region'] ?? '';
            } else {
                $completedOrder->billing_name = $data['billto_name'] ?? '';
                $completedOrder->billing_country = $data['billto_country'] ?? '';
                $completedOrder->billing_address = $data['billto_address'] ?? '';
                $completedOrder->billing_city = $data['billto_city'] ?? '';
                $completedOrder->billing_zip = $data['billto_postal'] ?? '';
                $completedOrder->billing_region = $data['billto_region'] ?? '';
            }
        } else {
            $completedOrder->customer_email = $data['customer_details']['email'];
            $completedOrder->total_amount = $data['amount_total'] / 100; // Since Stripe use the amount in cents, we need to convert it
            $completedOrder->payment_method = $data['payment_method_types'][0];
            $completedOrder->stripe_customer = $data['customer'] ?? '';

            // Dispatch for handle quantity reducing.
            foreach ($completedOrder->parts as $part) {
                QtyUpdated::dispatch($part['id'], $part['qty']);
            }
        }

        $completedOrder->save();

        return $completedOrder;
    }

    /**
     * @param  array  $params
     * @return bool
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update($params): bool
    {
        $order = $this->get($params);

        return $order && $order->fill($params)->save();
    }

    /**
     * @param  array  $params
     * @return CompletedOrder
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
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
