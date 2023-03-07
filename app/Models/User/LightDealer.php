<?php

namespace App\Models\User;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * Class LightDealer
 *
 * This LightDealer class is for Dealers on Nova
 *
 * @package App\Models\LightDealer
 * @todo This class is a workaround to avoid an unknown Eloquent relation within
 * App\Models\User\Dealer making the selector slowest render
 *
 * @property int $dealer_id
 * @property string $name
 * @property string $email
 *
 * @method static Builder whereIn($column, $values, $boolean = 'and', $not = false)
 */
class LightDealer extends Model
{
    use TableAware;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dealer';

    /**
     * @var bool
     */
    public $timestamps = false;

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
     * @return int
     */
    public function getDealerId(): int {
        return $this->dealer_id;
    }
}
