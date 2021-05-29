<?php

namespace App\Models\CRM\Dms\Printer;

use App\Models\Region;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Form
 * 
 * @package App\Models\CRM\Dms\Printer
 */
class Form extends Model
{
    public const FORM_TYPES = [
        'quote',
        'bill-of-sale'
    ];

    public const TABLE_NAME = "dms_printer_forms";

    public $timestamps = false;

    protected $table = self::TABLE_NAME;

    protected $fillable = [
        'name',
        'region',
        'type',
        'description',
        'department',
        'division',
        'website'
    ];

    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }


    /**
     * Region Code
     * 
     * @return BelongsTo
     */
    public function regionCode(): BelongsTo {
        return $this->belongsTo(Region::class, 'region', 'region_code');
    }


    /**
     * Get Label Attribute
     * 
     * @return string
     */
    public function getLabelAttribute(): string {
        $region = \ucwords(\strotolower($this->regionCode->region_name ?? ''));
        return ($region ? $region . ' ' : '') . $this->description;
    }
}