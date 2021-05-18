<?php

namespace App\Models\CRM\Dms\Printer;

use Illuminate\Database\Eloquent\Model;

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
}
