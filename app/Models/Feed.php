<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
    protected $table = 'feed';

    protected $primaryKey = 'id';

    protected $casts = [
        'last_run_at' => 'datetime'
    ];
}
