<?php

namespace App\Models\Geolocation;

use Illuminate\Database\Eloquent\Model;

class Geolocation extends Model
{
    public $timestamps = false;
    protected $table = 'geolocation';
    protected $connection = 'mysql';
    protected $fillable = [
        'zip',
        'latitude',
        'longitude',
        'city',
        'state',
        'country',
    ];
}
