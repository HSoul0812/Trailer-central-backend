<?php

namespace App\Models\Website\Forms;

use Illuminate\Database\Eloquent\Model;

class FieldMap extends Model
{
    // Define Table Name Constant
    const TABLE_NAME = 'website_form_field_map';

    // Define Mapping Types
    const MAP_TYPES = [
        'lead',
        'special_address',
        'trade',
        'lead_type'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'form_field',
        'map_field',
        'db_table'
    ];

    /**
     * Return Table Name
     * 
     * @return type
     */
    public static function getTableName() {
        return self::TABLE_NAME;
    }
}
