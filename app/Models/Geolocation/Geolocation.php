<?php

namespace App\Models\Geolocation;

use Illuminate\Database\Eloquent\Model;

class Geolocation extends Model
{
    protected $table = 'geolocation';
    public $timestamps = false;
    protected $connection = 'mysql';
    protected $fillable = [
        "zip",
        "latitude",
        "longitude",
        "city",
        "state",
        "country"
    ];
}
