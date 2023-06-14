<?php

namespace App\Nova\Resources\Location;

use App\Nova\Resource;
use Illuminate\Http\Request;

class Geolocation extends Resource
{
    public static $model = \App\Models\User\Location\Geolocation::class;

    public static $displayInNavigation = false;

    public static $search = [
        'zip'
    ];

    public function fields(Request $request)
    {
        return [];
    }

    public function title()
    {
        return "{$this->zip} {$this->city}, {$this->state}";
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }
}
