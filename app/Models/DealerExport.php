<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealerExport extends Model
{
    const STATUS_QUEUED = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_PROCESSED = 2;
    protected $fillable = [
        'dealer_id',
        'file_path',
        'entity_type',
        'status',
    ];
}
