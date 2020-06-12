<?php

namespace App\Models\Website;

use App\Models\Website\Website;
use Illuminate\Database\Eloquent\Model;

class Entity extends Model {
    
    const ENTITY_VIEW_HOME = 'Home';
    const ENTITY_VIEW_INVENTORY_LIST_SEARCH = 'Inventory-List-Search';
    const ENTITY_VIEW_LINK = 'Link';
    const ENTITY_VIEW_NOT_FOUND = 'Not-Found';

    protected $table = 'website_entity';

    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id', 'id');
    }
}
