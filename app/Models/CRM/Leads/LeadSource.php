<?php

namespace App\Models\CRM\Leads;

use Illuminate\Database\Eloquent\Model;

class LeadSource extends Model
{    
    
    const TABLE_NAME = 'crm_lead_sources';

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
    protected $primaryKey = 'lead_source_id';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'date_added';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = NULL;
        
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'source_name',
        'parent_id',
        'deleted'
    ];
    
    public static function getTableName() {
        return self::TABLE_NAME;
    }
}