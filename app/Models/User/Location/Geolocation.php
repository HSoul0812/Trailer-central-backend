<?php

namespace App\Models\User\Location;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $zip
 * @property float $latitude
 * @property float $longitude
 * @property string $city
 * @property string $state
 * @property string $country
 */
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
