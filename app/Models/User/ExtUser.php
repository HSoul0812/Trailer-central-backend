<?php

namespace App\Models\User;

use App\Models\User\DealerLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;

/**
 * Class ExtUser
 *
 * This ExtUser class is for Dealers on Nova
 *
 * @package App\Models\ExtUser
 *
 * @property int $dealer_id
 * @property string $name
 * @property string $email
 *
 * @method static Builder whereIn($column, $values, $boolean = 'and', $not = false)
 */
class ExtUser extends Model
{

    public const TABLE_NAME = 'dealer';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * @var int
     */
    protected $primaryKey = 'dealer_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_id',
        'name',
        'email',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * @var string
     */
    public static $title = 'name';

    /**
     * @var string[]
     */
    public static $search = [
        'dealer_id',
        'name',
    ];

    /**
     * @return string
     */
    public static function getTableName(): string {
        return self::TABLE_NAME;
    }

    /**
     * @return int
     */
    public function getDealerId(): int {
        return $this->dealer_id;
    }
}
