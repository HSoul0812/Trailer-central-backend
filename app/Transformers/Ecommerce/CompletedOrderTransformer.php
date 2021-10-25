<?php
namespace App\Transformers\Ecommerce;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Repositories\Parts\Textrail\PartRepository;
use League\Fractal\TransformerAbstract;

class CompletedOrderTransformer extends TransformerAbstract
{
    /** @var PartRepository */
    private $textRailPartRepository;

    /**
     * CompletedOrderTransformer constructor.
     * @param PartRepository $textRailPartRepository
     */
    public function __construct(PartRepository $textRailPartRepository)
    {
        $this->textRailPartRepository = $textRailPartRepository;
    }

    public function transform(CompletedOrder $completedOrder)
    {
        $parts = $completedOrder->parts;

        $partCollection = [];

        $totalQty = 0;
        if (!empty($parts)) {
            foreach ($parts as $part) {
                $partCollection[] = $this->textRailPartRepository->getById($part['id']);
                $totalQty += $part['qty'];
            }
        }

        return [
            'id' => $completedOrder->id,
            'hook_event_id' => $completedOrder->event_id,
            'object_id' => $completedOrder->object_id,
            'customer_email' => $completedOrder->customer_email,
            'total_amount' => $completedOrder->total_amount,
            'payment_method' => $completedOrder->payment_method,
            'payment_status' => $completedOrder->payment_status,
            'stripe_customer_id' => $completedOrder->stripe_customer,
            'shipping_address' => $completedOrder->shipping_address,
            'shipping_country' => $completedOrder->shipping_country,
            'shipping_city' => $completedOrder->shipping_city,
            'shipping_zip' => $completedOrder->shipping_zip,
            'shipping_region' => $completedOrder->shipping_region,
            'billing_address' => $completedOrder->billing_address,
            'billing_country' => $completedOrder->billing_country,
            'billing_city' => $completedOrder->billing_city,
            'billing_zip' => $completedOrder->billing_zip,
            'billing_region' => $completedOrder->billing_region,
            'created_at' => $completedOrder->created_at,
            'status' => $completedOrder->status,
            'invoice_id' => $completedOrder->invoice_id,
            'invoice_url' => $completedOrder->invoice_url,
            'parts' => $partCollection,
            'total_qty' => $totalQty,
        ];
    }
}