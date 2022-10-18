<?php


namespace App\Models\Pos;

use App\Models\CRM\User\Customer;
use App\Models\CRM\User\SalesPerson;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ParkedSale
 *
 * For a POS Parked Sale
 *
 * @package App\Models\Pos
 * @property Customer $customer
 * @property SalesPerson $salesPerson
 */
class ParkedSale extends Model
{
    use TableAware;

    protected $table = "crm_pos_parked_sales";


    public function customer()
    {
        return $this->belongsTo(Customer::class,'id', 'customer_id');
    }

    public function salesPerson()
    {
        return $this->belongsTo(SalesPerson::class, 'id', 'sales_person_id');
    }
}
