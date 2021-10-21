<?php

namespace App\Models\Website\User;

use App\Models\Inventory\Inventory;
use Illuminate\Database\Eloquent\Model;

class WebsiteUserFavoriteInventory extends Model
{
    //
    protected $fillable = [
        'website_user_id',
        'inventory_id'
    ];
    protected $table = 'website_user_favorite_inventory';

    public function websiteUser() {
        return $this->belongsTo(WebsiteUser::class, 'website_user_id');
    }

    public function inventory() {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }
}
