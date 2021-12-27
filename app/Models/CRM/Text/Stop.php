<?php

namespace App\Models\CRM\Text;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Text Stop
 *
 * @package App\Models\CRM\Text
 */
class Stop extends Model
{
    /**
     * Statuses for Text Stop
     * 
     * @var array
     */
    const REPORT_TYPES = ['unsubscribed', 'invalid'];

    /**
     * Statuses for Lead Assign
     * 
     * @var array
     */
    const REPORT_TYPE_DEFAULT = 'unsubscribed';

    /**
     * @var string
     */
    const TABLE_NAME = 'crm_text_reports';

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
        'lead_id',
        'text_id',
        'sms_number',
        'type'
    ];

    /**
     * Get Table Name
     * 
     * @return string
     */
    public static function getTableName() {
        return self::TABLE_NAME;
    }
}