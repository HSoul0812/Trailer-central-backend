<?php
namespace App\Models\CRM\Dms\Payment;


use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

class DealerRefund extends Model
{
    use TableAware;

    protected $table = 'dealer_refunds';
}