<?php

namespace App\Models\User;
use Illuminate\Database\Eloquent\Model;

class DealerXmlExport extends Model {
    
     public const EXPORT_INACTIVE = 0;
     public const EXPORT_ACTIVE = 1;
     
     const TABLE_NAME = 'dealer_xml_export';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = "id";
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_id',
        'export_active'
    ];
    /**
     * @var bool
     */
    public $timestamps = false;
    
}
