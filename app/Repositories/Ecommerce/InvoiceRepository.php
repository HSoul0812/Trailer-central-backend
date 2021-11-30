<?php

namespace App\Repositories\Ecommerce;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use Illuminate\Database\Eloquent\Builder;
use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Support\Facades\DB;

class InvoiceRepository implements InvoiceRepositoryInterface
{

  /**     
   * @var App\Models\Ecommerce\CompletedOrder\CompletedOrder
   */
   
  protected $model;

  public function __construct(CompletedOrder $model) {
      $this->model = $model;
      $this->httpClient = new GuzzleHttpClient();
  }

  public function create($params) {
      throw new NotImplementedException;
  }

  public function delete($params) {
      throw new NotImplementedException;
  }

  public function update($params) {
      throw new NotImplementedException;
  }

  public function getAll($params) {
      throw new NotImplementedException;
  }

  /**
   * @param array $params
   * @return string
   *
   * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
   * @throws \InvalidArgumentException when "id" was not provided
   */
  public function get($params)
  {
      if (isset($params['id'])) {
          $completedOrder = $this->model->findOrFail($params['id']);

          if ($completedOrder->invoice_pdf_url) {
            
            return $completedOrder;
            
          } elseif ($completedOrder->invoice_id && !$completedOrder->invoice_pdf_url) {
            $stripe_secret = DB::table('stripe_checkout_credentials')->first()->secret;
            $endpoint = 'https://api.stripe.com/v1/invoices/' . $completedOrder->invoice_id;
            
            $response = $this->httpClient->get($endpoint, ['headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $stripe_secret]]);
            $invoice = json_decode($response->getBody()->getContents(), true);
            
            $completedOrder->invoice_pdf_url = $invoice['invoice_pdf'];
            $completedOrder->save();
            
            return $completedOrder;
          } else {
            throw new \InvalidArgumentException('InvoiceRepository::get invoice is not ready at the moment');
          }
      }

      throw new \InvalidArgumentException('InvoiceRepository::get requires argument of: "id"');
  }

}  