<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use App\Models\Inventory\EntityType;

class Category extends Model {
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_category';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'label',
        'legacy_category',
        'entity_type_id',
        'category',
    ];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'inventory_category_id';

    public $timestamps = false;

    public function entityType()
    {
        return $this->hasOne(EntityType::class, 'entity_type_id', 'entity_type_id');
    }

}
