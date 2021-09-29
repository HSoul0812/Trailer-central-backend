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
    protected $table = 'ecommerce_completed_orders';

    protected $fillable = [
        'customer_email',
        'total_amount',
        'payment_method',
        'payment_status',
        'event_id',
        'object_id',
        'stripe_customer',
        'shipping_address',
        'shipping_name',
        'shipping_country',
        'shipping_city',
        'shipping_region',
        'shipping_zip',
        'billing_address',
        'billing_name',
        'billing_country',
        'billing_city',
        'billing_region',
        'billing_zip',
        'parts'
    ];
}