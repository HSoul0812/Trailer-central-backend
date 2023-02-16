<?php

namespace App\Models\Dealer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewedDealer extends Model
{
    use HasFactory;

    protected $fillable = [
        'dealer_id',
        'name',
    ];
}
