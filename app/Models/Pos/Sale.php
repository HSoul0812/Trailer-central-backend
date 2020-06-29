<?php


namespace App\Models\Pos;


use App\Models\CRM\Dms\Refund;
use App\Models\CRM\Quickbooks\PaymentMethod;
use App\Models\CRM\User\Customer;
use App\Models\CRM\User\SalesPerson;
use App\Utilities\JsonApi;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Sales
 *
 * For a POS-based sale
 *
 * @package App\Models\Pos
 * @property Register $register
 * @property Customer $customer
 * @property SalesPerson $salesPerson
 * @property PaymentMethod $paymentMethod
 * @property SaleProducts[] $products
 * @property Refund[] $refunds
 */
class Sale extends Model implements JsonApi\Filterable
{
    protected $table = "crm_pos_sales";

    protected $filterableColumns = ['*'];

    /**
     * the `Register` (pos register session) that made this sale
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function register()
    {
        return $this->hasOne(Register::class, 'id', 'register_id');
    }

    /**
     * Products in this sale
     */
    public function products()
    {
        return $this->hasMany(SaleProducts::class, 'sale_id');
    }

    public function customer()
    {
        return $this->hasOne(Customer::class,'id', 'customer_id');
    }

    public function salesPerson()
    {
        return $this->hasOne(SalesPerson::class, 'id', 'sales_person_id');
    }

    public function paymentMethod()
    {
        return $this->hasOne(PaymentMethod::class, 'id', 'payment_method_id');
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class, 'tb_primary_id');
    }

    public function jsonApiFilterableColumns(): ?array
    {
        return $this->filterableColumns;
    }
}
