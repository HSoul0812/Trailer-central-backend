<?php

namespace App\Models\CRM\Dms\Printer;

use App\Models\Region;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Okidata
 * 
 * @package App\Models\CRM\Dms\Printer
 */
class Okidata extends Model
{
    public const TABLE_NAME = "dms_okidata_forms";

    public $timestamps = false;

    protected $table = self::TABLE_NAME;

    protected $fillable = [
        'name',
        'region',
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
