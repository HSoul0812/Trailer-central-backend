<?php

namespace App\Services\Ecommerce\Invoice;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use Illuminate\Database\Eloquent\Builder;
use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class InvoiceService implements InvoiceServiceInterface
{

    public function __construct()
    {
        $this->httpClient = new GuzzleHttpClient();
    }

    public function getStripeInvoice(CompletedOrder $completedOrder): array
    {
      $stripe_invoice_url = config('ecommerce.textrail.stripe_invoice_url');
      $stripe_secret = DB::table('stripe_checkout_credentials')->first()->secret;
      $endpoint = $stripe_invoice_url . $completedOrder->invoice_id;
      
      $response = $this->httpClient->get($endpoint, ['headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $stripe_secret]]);
      
      return json_decode($response->getBody()->getContents(), true);
    }

}