<?php

namespace App\Repositories\Ecommerce;

use App\Repositories\Repository;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;

interface InvoiceRepositoryInterface extends Repository
{
  /**
   * @param array $params
   * @return string
   *
   * @throws \InvalidArgumentException
   */
  public function get($params);
}