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
    public $timestamps = false;

    protected $table = "dms_okidata_forms";

    protected $fillable = [
        'name',
        'region',
        'description',
        'department',
        'division',
        'website'
    ];


    /**
     * Region
     * 
     * @return BelongsTo
     */
    public function region(): BelongsTo {
        return $this->belongsTo(Region::class, 'region', 'region_code');
    }
}
