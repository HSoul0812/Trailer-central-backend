<?php

namespace App\Repositories\Payment;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface PaymentLogRepositoryInterface
{
    public function create($params): Builder|Model;
}
