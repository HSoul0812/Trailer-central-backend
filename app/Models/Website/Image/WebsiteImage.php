<?php

namespace App\Models\Website\Image;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class WebsiteImage
 * @package App\Models\User
 *
 * @property int $identifier
 * @property string $title
 * @property string $image
 * @property string $description
 * @property string $link
 * @property int $sort_order
 * @property int $is_active
 * @property int $promo_id
 * @property string $date_created
 * @property string $expires_at
 * @property string $starts_from
 *
 * @method static \Illuminate\Database\Query\Builder select($columns = ['*'])
 * @method static \Illuminate\Database\Query\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static \Illuminate\Database\Query\Builder whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static WebsiteImage findOrFail($id, array $columns = ['*'])
 * @method static WebsiteImage|Collection|static[]|static|null find($id, $columns = ['*'])
 */
class WebsiteImage extends Model
{
    use TableAware;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'website_image';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'identifier';

    /**
     * Timestamps generated.
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'website_id',
        'title',
        'image',
        'description',
        'link',
        'sort_order',
        'date_created',
        'is_active',
        'promo_id',
        'expires_at',
        'starts_from'
    ];
}
