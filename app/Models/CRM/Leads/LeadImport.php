<?php
namespace App\Models\CRM\Leads;

use App\Models\User\User;
use App\Models\User\DealerLocation;
use App\Models\Website\Website;
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

    /**
     * LeadImport BelongsTo User
     * 
     * @return BelongsTo
     */
    public function dealer()
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }

    /**
     * LeadImport BelongsTo DealerLocation
     * 
     * @return BelongTo
     */
    public function dealerLocation()
    {
        return $this->belongsTo(DealerLocation::class, 'dealer_location_id', 'dealer_location_id');
    }

    /**
     * LeadImport BelongsTo Website
     * 
     * @return BelongsTo
     */
    public function website()
    {
        return $this->belongsTo(Website::class, 'id', 'dealer_id');
    }

    public static function getTableName() {
        return self::TABLE_NAME;
    }
}
