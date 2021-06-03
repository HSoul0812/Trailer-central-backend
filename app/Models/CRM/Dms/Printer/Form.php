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

    public const SHORT_BODY = [
        'aluminum' => 'Alum',
        'steel_frame_aluminum' => 'StlAlum',
        'composite' => 'Comp',
        'steel' => 'Steel',
        'galvanized' => 'Galv'
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
}