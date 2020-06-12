<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class Image extends Model {
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'image';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'image_id';
}
