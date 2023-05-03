<?php

declare(strict_types=1);

namespace App\Models\Glossary;

use App\Support\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

class Glossary extends Model
{
    use TableAware;
    protected $table = 'glossary';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'denomination',
        'short_description',
        'long_description',
        'type',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
      'pivot',
    ];
}
