<?php

namespace App\Models\Inventory\Floorplan;

use Illuminate\Database\Eloquent\Model;

use App\Models\CRM\Dms\Quickbooks\Account;
use App\Models\Inventory\Inventory;

class Payment extends Model
{

    const PAYMENT_CATEGORIES = [
        'Balance' => 'balance',
        'Interest' => 'interest',
    ];

    protected $table = 'inventory_floor_plan_payment';

    protected $guarded = ['qb_id'];

    public $updated_at = false;

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'inventory_id');
    }

}
