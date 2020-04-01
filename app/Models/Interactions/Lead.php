<?php

namespace App\Models\Interactions;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $table = 'website_lead';

    public function customer()
    {
        return $this->hasOne(Customer::class, 'customer_id', 'customer_id');
    }
}
