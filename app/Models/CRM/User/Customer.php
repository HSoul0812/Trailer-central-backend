<?php

namespace App\Models\CRM\User;

use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\Dms\UnitSale;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    protected $table = 'dms_customer';

    public $timestamps = false;

    public function quotes()
    {
        return $this->hasMany(UnitSale::class, 'buyer_id', 'id');
    }
    
    public function openQuotes()
    {
        return $this->quotes()->where('is_archived', 0)
                                ->where(function($query) {
                                    $query->whereDoesntHave('payments')
                                            ->orWhereHas('payments', function($query) {
                                                $query->select(DB::raw('sum(amount) as paid_amount'))
                                                    ->groupBy('invoice_id')
                                                    ->havingRaw('paid_amount < dms_unit_sale.total_price or paid_amount <= 0');
                                            });
                                });
                                
    }
}
