<?php

namespace App\Services\Ecommerce\Invoice;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use Illuminate\Database\Eloquent\Builder;
use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Support\Facades\DB;

class InvoiceService implements InvoiceServiceInterface
{

    public function __construct()
    {
        $this->httpClient = new GuzzleHttpClient();
    }

    public function getStripeInvoice(CompletedOrder $completedOrder): array
    {
      $stripe_secret = DB::table('stripe_checkout_credentials')->first()->secret;
      $endpoint = CompletedOrder::STRIPE_INVOICE_URL . $completedOrder->invoice_id;
      
      $response = $this->httpClient->get($endpoint, ['headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $stripe_secret]]);
      
      return json_decode($response->getBody()->getContents(), true);
    }

}