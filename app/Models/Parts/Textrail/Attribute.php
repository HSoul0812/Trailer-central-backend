<?php

namespace App\Models\Parts\Textrail;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\TableAware;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 */
class Attribute extends Model
{
    use TableAware;

    protected $table = 'textrail_attributes';

    protected $fillable = [
        'name',
        'code'
    ];
}
