<?php
namespace App\Models\CRM\Leads\Export;

use Illuminate\Database\Eloquent\Model;

class LeadEmail extends Model
{
    const EXPORT_FORMAT_ADF = 0;
    const EXPORT_FORMAT_VINSOLUTIONS = 1;
    const EXPORT_FORMAT_IDS = 2;
    
    public const EXPORT_FORMAT_TYPES = [
        self::EXPORT_FORMAT_ADF,
        self::EXPORT_FORMAT_VINSOLUTIONS,
        self::EXPORT_FORMAT_IDS
    ];
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lead_email';

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
        'email',
        'export_format',
        'cc_email',
        'dealer_location_id'
    ];
    
    public $timestamps = false;
    
    public function getToEmailsAttribute() : array
    {
        return $this->email ? explode(',', $this->email) : [];
    }
    
    public function getCopiedEmailsAttribute() : array
    {
        return $this->cc_email ? explode(',', $this->cc_email) : [];
    }
}
