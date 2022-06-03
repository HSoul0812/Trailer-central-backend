<?php

namespace App\Http\Controllers\v1\Webhook;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;

class SubscriptionController extends CashierController {

    /**
     * Handle customer subscription updated.
     *
     * @param array $payload
     * @return array
     */
    public function handleCustomerSubscriptionUpdated(array $payload): array
    {
        // Handle The Event
        return $payload;
    }

    /**
     * Handle customer subscription deleted.
     *
     * @param array $payload
     * @return array
     */
    public function handleCustomerSubscriptionDeleted(array $payload): array
    {
        // Handle The Event
        return $payload;
    }

}
