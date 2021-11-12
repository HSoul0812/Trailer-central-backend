<?php

namespace App\Models\User\Location;

use Illuminate\Database\Eloquent\Model;

class Geolocation extends Model
{
    protected $table = 'geolocation';
    public $timestamps = false;
    protected $fillable = [
        "zip",
        "latitude",
        "longitude",
        "city",
        "state",
        "country"
    ];
}
