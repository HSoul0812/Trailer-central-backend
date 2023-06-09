<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecaptchaLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'score',
        'user_agent',
        'ip',
        'action',
        'path',
    ];
}
