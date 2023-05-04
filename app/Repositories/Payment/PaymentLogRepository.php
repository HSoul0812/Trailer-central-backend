<?php

namespace App\Repositories\Payment;

use App\Models\Payment\PaymentLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaymentLogRepository implements PaymentLogRepositoryInterface
{
    public function __construct(private PaymentLog $model)
    {
    }

    public function create($params): Builder|Model
    {
        return $this->model->newQuery()->create($params);
    }
}
