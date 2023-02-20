<?php

namespace App\Models\Dealer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewedDealer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'dealer_id',
        'inventory_id',
    ];
}
