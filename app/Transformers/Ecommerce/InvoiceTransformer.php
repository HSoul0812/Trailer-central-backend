<?php


namespace App\Transformers\Ecommerce;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use League\Fractal\TransformerAbstract;

class InvoiceTransformer extends TransformerAbstract
{
  public function transform(CompletedOrder $completedOrder): array
  {
      return [
          'id' => $completedOrder->id,
          'invoice_pdf_url' => $completedOrder->invoice_pdf_url
      ];
  }
}