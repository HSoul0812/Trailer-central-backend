<?php
namespace App\Models\Ecommerce\CompletedOrder;


use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

class CompletedOrder extends Model
{
    use TableAware;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stripe_completed_orders';

    protected $fillable = [
        'customer_email',
        'total_amount',
        'payment_method',
        'payment_status',
        'event_id',
        'object_id',
        'stripe_customer',
        'shipping_address',
        'billing_address',
        'postal_code'
    ];
}