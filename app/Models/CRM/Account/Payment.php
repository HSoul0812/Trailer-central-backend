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

    public $timestamps = false;


    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'id', 'invoice_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo('App\Models\CRM\Quickbooks\PaymentMethod');
    }
}
