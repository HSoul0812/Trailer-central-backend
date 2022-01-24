<?php

declare(strict_types=1);

namespace App\Transformers\Ecommerce;

use App\Http\Requests\Ecommerce\CreateCompletedOrderRequest;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use League\Fractal\TransformerAbstract;

class CompleteOrderRequestTransformer extends TransformerAbstract
{
    public const NO_BILLING = 1;

    public function transform(CreateCompletedOrderRequest $request): array
    {
        $data = $request->data['object'];

        $isStripeCall = !isset($data['parts']);

        $firstName = $data['shipto_first_name'] ?? '';
        $lastName = $data['shipto_last_name'] ?? '';

        $fullName = $firstName . ' ' . $lastName;

        $result = [
            // Coming from dealer site
            'dealer_id' => $request->dealer_id,
            'object_id' => $data['id'],
            'event_id' => $request->id,
            'parts' => $isStripeCall ? [] : json_decode($data['parts'], true),

            'invoice_id' => $data['invoice_id'] ?? '',
            'invoice_url' => $data['invoice_url'] ?? '',
            'invoice_pdf_url' => $data['invoice_pdf_url'] ?? '',
            'shipping_name' => $fullName,
            'shipping_country' => $data['shipto_country'] ?? '',
            'shipping_address' => $data['shipto_address'] ?? '',
            'shipping_city' => $data['shipto_city'] ?? '',
            'shipping_zip' => $data['shipto_postal'] ?? '',
            'shipping_region' => $data['shipto_region'] ?? '',
            'tax' => $data['tax'] ?? 0,
            'tax_rate' => $data['tax_rate'] ?? 0,
            'total_before_tax' => $data['total_before_tax'] ?? 0,
            'handling_fee' => $data['handling_fee'] ?? 0,
            'shipping_fee' => $data['shipping_fee'] ?? 0,
            'subtotal' => $data['subtotal'] ?? 0,
            'in_store_pickup' => $data['in_store_pickup'] ?? 0,
            'ecommerce_cart_id' => $data['cart_id'] ?? 0,
            'ecommerce_customer_id' => !empty($data['customer_id']) ? $data['customer_id'] : null,
            'ecommerce_items' => isset($data['session_cart_items']) ? json_decode($data['session_cart_items'], true) : [],
            'shipping_carrier_code' => !empty($data['shipping_carrier_code']) ? $data['shipping_carrier_code'] : null,
            'shipping_method_code' => !empty($data['shipping_method_code']) ? $data['shipping_method_code'] : null,

            // Coming from Stripe
            'customer_email' => isset($data['customer_details']) ? $data['customer_details']['email'] : '',
            // Since Stripe use the amount in cents, we need to convert it
            'total_amount' => $isStripeCall ? ($data['amount_total'] / 100) : $data['amount_total'],
            'payment_method' => isset($data['payment_method_types']) ? $data['payment_method_types'][0] : '',
            'stripe_customer' => $data['customer'] ?? '',
            'payment_status' => $data['payment_status'] ?? CompletedOrder::PAYMENT_STATUS_UNPAID,
            'payment_intent' => $data['payment_intent'] ?? null,
            'phone_number' => $data['phone_number'] ?? '',
        ];

        // also coming from dealer site
        if (isset($data['no-billing']) && (int)$data['no-billing'] === self::NO_BILLING) {
            $result = array_merge($result, ['billing_name' => $data['shipto_name'] ?? '',
                    'billing_country' => $data['shipto_country'] ?? '',
                    'billing_address' => $data['shipto_address'] ?? '',
                    'billing_city' => $data['shipto_city'] ?? '',
                    'billing_zip' => $data['shipto_postal'] ?? '',
                    'billing_region' => $data['shipto_region'] ?? ''
                ]
            );
        } else {
            $result = array_merge($result, ['billing_name' => $data['billto_name'] ?? '',
                    'billing_country' => $data['billto_country'] ?? '',
                    'billing_address' => $data['billto_address'] ?? '',
                    'billing_city' => $data['billto_city'] ?? '',
                    'billing_zip' => $data['billto_postal'] ?? '',
                    'billing_region' => $data['billto_region'] ?? '']
            );
        }

        return $result;
    }
}
