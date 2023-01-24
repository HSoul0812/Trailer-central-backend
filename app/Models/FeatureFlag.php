<?php

namespace App\Models;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $code
 * @property boolean $is_enabled
 * @property Carbon $created_at
 *
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static FeatureFlag    find($id, array $columns = ['*'])
 * @method static FeatureFlag    first()
 */
class FeatureFlag extends Model
{
    use TableAware;

    private const TABLE_NAME = 'simple_feature_flag';

    /** @var string */
    public const CREATED_AT = 'created_at';

    /** @var string to avoid touching the model on updates */
    public const UPDATED_AT = null;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'code';

    /**
     * Primary Key Doesn't Auto Increment
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Set String Primary Key
     *
     * @var string
     */
    protected $keyType = 'string';

    /** @var array<string> */
    protected $fillable = ['code', 'is_enabled'];

    /** @var array<string,string> */
    protected $casts = ['is_enabled' => 'boolean'];

    /** @var array<string> */
    protected $dates = ['created_at'];

    /** @var array<string> */
    protected $hidden = ['created_at'];

    /** @var boolean */
    public $timestamps = false;

    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }
}
