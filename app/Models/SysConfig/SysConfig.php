<?php

namespace App\Models\SysConfig;

use App\Support\Traits\TableAware;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SysConfig extends Model
{
    use HasFactory;
    use TableAware;
    protected $fillable = [
        'key',
        'value',
    ];
}
