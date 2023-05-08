<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $url
 * @property \DateTimeInterface|Carbon $created_at
 * @property \DateTimeInterface|Carbon $dropped_at
 *
 * @method static Builder select($columns = ['*'])
 * @method static self find(int $id)
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static Builder whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static Collection|static create(array $attributes = [])
 * @method static static findOrFail($id, $columns = ['*'])
 */
class StorageObjectToDrop extends Model
{
    /** @var string */
    protected $table = 'storage_object_to_drop';

    /** @var string */
    public const UPDATED_AT = null;

    /** @var array The attributes that are mass assignable. */
    protected $fillable = [
        'url',
        'dropped_at',
    ];

    /** @var array  The attributes that should be mutated to dates. */
    protected $dates = [
        'dropped_at',
    ];
}
