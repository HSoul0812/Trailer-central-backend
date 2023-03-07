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

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'date_modified';

    protected $fillable = [
        'website_id',
        'entity_type',
        'entity_view',
        'template',
        'parent',
        'title',
        'url_path',
        'url_path_external',
        'sort_order',
        'in_nav',
        'is_active',
        'deleted',
        'meta_description',
        'meta_keywords',
        'entity_config'
    ];

    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id', 'id');
    }
}
