<?php
namespace App\Transformers\Ecommerce;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Repositories\Parts\PartRepositoryInterface;
use App\Repositories\Parts\Textrail\PartRepository;
use League\Fractal\TransformerAbstract;

class CompletedOrderTransformer extends TransformerAbstract
{
    /** @var PartRepository */
    private $textRailPartRepository;

    /**
     * CompletedOrderTransformer constructor.
     * @param PartRepositoryInterface $textRailPartRepository
     */
    public function __construct(PartRepositoryInterface $textRailPartRepository)
    {
        $this->textRailPartRepository = $textRailPartRepository;
    }

    public function transform(CompletedOrder $completedOrder): array
    {
        $partIds = collect($completedOrder->parts)->map(static function (array $part): int {
            return $part['id'];
        })->toArray();

        $partCollection = $this->textRailPartRepository->getAllByIds($partIds);

        return [
            'id' => $completedOrder->id,
            'hook_event_id' => $completedOrder->event_id,
            'object_id' => $completedOrder->object_id,
            'customer_email' => $completedOrder->customer_email,
            'total_amount' => $completedOrder->total_amount,
            'payment_method' => $completedOrder->payment_method,
            'payment_status' => $completedOrder->payment_status,
            'payment_intent' => $completedOrder->payment_intent,
            'refund_status' => $completedOrder->refund_status,
            'refunded_amount' => $completedOrder->refunded_amount,
            'refunded_parts' => $completedOrder->refunded_parts,
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
            'parts' => $partCollection,
        ];
    }
}
