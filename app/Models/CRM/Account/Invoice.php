<?php


namespace App\Models\CRM\Account;


use App\Models\CRM\Dms\UnitSale;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'qb_invoices';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    public function unitSale()
    {
        return $this->hasMany(UnitSale::class, 'id', 'unit_sale_id');
    }

    public function payment()
    {
        return $this->hasMany(Payment::class, 'invoice_id', 'id');
    }
}
