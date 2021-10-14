<?php
namespace App\Transformers\Ecommerce;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Repositories\Ecommerce\CompletedOrderRepositoryInterface;
use App\Repositories\Parts\Textrail\PartRepository;
use App\Transformers\Parts\Textrail\PartsTransformer;
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
        $parts = json_decode($completedOrder->parts, true);

        $partCollection = [];
        foreach ($parts as $part) {
            $partCollection[] = $this->textRailPartRepository->getById($part['id']);
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
            'billing_address' => $completedOrder->billing_address,
            'postal_code' => $completedOrder->postal_code,
            'created_at' => $completedOrder->created_at,
            'status' => $completedOrder->status,
            'parts' => $partCollection,
        ];
    }
}