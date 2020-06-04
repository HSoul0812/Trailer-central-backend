<?php


namespace App\Models\CRM\Dms;


use App\Models\CRM\Account\Invoice;
use App\Models\CRM\Leads\Lead;
use Illuminate\Database\Eloquent\Model;

class UnitSale extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dms_unit_sale';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    public function lead()
    {
        return $this->hasMany(Lead::class, 'identifier', 'lead_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'unit_sale_id', 'id');
    }
}
