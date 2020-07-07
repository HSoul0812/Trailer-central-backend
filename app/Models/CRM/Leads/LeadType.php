<?php


namespace App\Models\CRM\Leads;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\TableAware;

class LeadType extends Model
{
    use TableAware;
    
    const TYPE_GENERAL = 'general';
    const TYPE_CRAIGSLIST = 'craigslist';
    const TYPE_INVENTORY = 'inventory';
    const TYPE_TEXT = 'text';
    const TYPE_SHOWROOM_MODEL = 'showroomModel';
    const TYPE_JOTFORM = 'jotform';
    const TYPE_BUILD = 'build';
    const TYPE_RENTALS = 'rentals';
    const TYPE_FINANCING = 'financing';
    const TYPE_SERVICE = 'service';
    const TYPE_CALL = 'call';
    const TYPE_TRADE = 'trade';
    
    const TABLE_NAME = 'website_lead_types';
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
    protected $primaryKey = 'lead_type_id';

    /**
     * Get lead.
     */
    public function lead()
    {
        return $this->hasOne(LeadType::class, 'identifier', 'lead_id');
    }
    
    public static function getTableName() {
        return self::TABLE_NAME;
    }
}
