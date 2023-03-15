<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DealerExport
 *
 * @package App\Models
 */
class DealerExport extends Model
{
    const STATUS_QUEUED = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_PROCESSED = 2;
    const STATUS_ERROR = 3;

    protected $fillable = [
        'dealer_id',
        'file_path',
        'entity_type',
        'status',
        'zip_password',
    ];
}
