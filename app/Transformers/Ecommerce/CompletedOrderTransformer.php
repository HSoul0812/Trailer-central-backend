<?php
namespace App\Transformers\Ecommerce;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Models\Parts\Part;
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
        $partsSummary = $this->getPartSummary($completedOrder);

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
            'max_refundable_amount' => $completedOrder->total_amount - (float) $completedOrder->refunded_amount,
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
            'invoice_pdf_url' => $completedOrder->invoice_pdf_url,
            'parts' => $partsSummary['parts'],
            'total_qty' => $partsSummary['total_qty'],
            'tax' => $partsSummary['tax'],
            'tax_rate' => $partsSummary['tax_rate'],
            'total_before_tax' => $partsSummary['total_before_tax'],
            'shipping_fee' => $partsSummary['shipping_fee'],
            'handling_fee' => $partsSummary['handling_fee'],
            'subtotal' => $partsSummary['subtotal'],
            'in_store_pickup' => $partsSummary['in_store_pickup'],
            'phone_number' => $completedOrder->phone_number,
        ];
    }

    /**
     * @param CompletedOrder $order
     * @return array{parts: array, total_qty: int}
     */
    public function getPartSummary(CompletedOrder $order): array
    {
        $partCollection = [];
        $totalQty = 0;

        if (!empty($order->parts)) {
            $indexedParts = [];
            $partIds = collect($order->parts)->map(static function (array $part) use (&$totalQty, &$indexedParts): int {
                $totalQty += $part['qty'];
                $indexedParts[$part['id']] = $part;

                return $part['id'];
            })->toArray();

            $partCollection = $this->textRailPartRepository
                ->getAllByIds($partIds)
                ->map(static function (Part $part) use (&$indexedParts): array {
                    return array_merge($indexedParts[$part->id], [
                        'sku' => $part->sku,
                        'title' => $part->title,
                        'description' => $part->description
                    ]);
                });
        }

        return [
            'total_qty' => $totalQty,
            'parts' => $partCollection,
            'tax' => $order->tax,
            'tax_rate' => $order->tax_rate,
            'total_before_tax' => $order->total_before_tax,
            'shipping_fee' => $order->shipping_fee,
            'handling_fee' => $order->handling_fee,
            'subtotal' => $order->subtotal,
            'in_store_pickup' => $order->in_store_pickup
        ];
    }
}
