<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Str;

class AppToken extends Model
{
    use HasFactory;

    public const TOKEN_LENGTH = 32;

    protected $fillable = [
        'app_name',
        'token',
    ];

    public static function createWithAppName(string $appName): AppToken
    {
        return AppToken::create([
            'app_name' => $appName,
            'token' => Str::random(self::TOKEN_LENGTH),
        ]);
    }
}
