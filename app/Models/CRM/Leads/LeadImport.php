<?php
namespace App\Models\CRM\Leads;

use App\Models\User\User;
use App\Models\Website\Website;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

class LeadImport extends Model
{
    use TableAware;

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
     * LeadImport BelongsTo Website
     * 
     * @return BelongsTo
     */
    public function website()
    {
        return $this->belongsTo(Website::class, 'dealer_id', 'dealer_id');
    }
}
