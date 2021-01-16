<?php
namespace App\Models\CRM\Leads;

use Illuminate\Database\Eloquent\Model;

class LeadImport extends Model
{

    const TABLE_NAME = 'lead_import';

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
    protected $primaryKey = 'id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_id',
        'dealer_location_id',
        'email'
    ];

    public static function getTableName() {
        return self::TABLE_NAME;
    }
}
