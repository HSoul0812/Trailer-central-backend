<?php


namespace App\Models\CRM\Account;


use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'qb_payment';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'id', 'invoice_id');
    }
}
