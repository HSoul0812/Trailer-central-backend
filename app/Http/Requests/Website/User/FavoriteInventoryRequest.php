<?php
namespace App\Http\Requests\Website\User;

use App\Http\Requests\Request;

class FavoriteInventoryRequest extends Request
{
    protected $rules = [
        'inventory_ids' => 'array|required',
        'inventory_ids.*' => 'integer'
    ];
}
