<?php

namespace App\Models\CRM\Account;

use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\Dms\UnitSale;

class Invoice extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'qb_invoices';

    public $timestamps = false;

    public function unitSale()
    {
        return $this->hasOne(UnitSale::class, 'id', 'unit_sale_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
