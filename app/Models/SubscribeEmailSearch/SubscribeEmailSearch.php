<?php

declare(strict_types=1);

namespace App\Models\SubscribeEmailSearch;

use App\Support\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

class SubscribeEmailSearch extends Model
{
    use TableAware;
    protected $table = 'subscribe_email_search';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'url',
        'subscribe_email_sent',
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
