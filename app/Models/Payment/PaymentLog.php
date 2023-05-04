<?php

namespace App\Models\Payment;

use App\Support\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    use TableAware;

    protected $fillable = [
        'payment_id',
        'client_reference_id',
        'full_response',
        'plan_key',
        'plan_name',
        'plan_duration',
    ];
}
