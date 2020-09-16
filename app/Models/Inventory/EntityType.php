<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class EntityType extends Model {
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eav_entity_type';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'entity_type_id';

    public $timestamps = false;

}
