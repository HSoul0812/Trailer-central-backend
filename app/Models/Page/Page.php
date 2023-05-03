<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    public $timestamps = false;
    protected $table = 'pages';

    protected $fillable = [
        'name',
        'url',
    ];
}
