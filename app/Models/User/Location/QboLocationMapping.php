<?php

declare(strict_types=1);

namespace App\Models\User\Location;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $dealer_id
 * @property int $dealer_location_id
 * @property int $quickbooks_id
 *
 * @method static \Illuminate\Database\Query\Builder select($columns = ['*'])
 * @method static \Illuminate\Database\Query\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static \Illuminate\Database\Query\Builder whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static QboLocationMapping findOrFail($id, array $columns = ['*'])
 * @method static QboLocationMapping|Collection|static[]|static|null find($id, $columns = ['*'])
 */
class QboLocationMapping extends Model
{
    use TableAware;

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'exported_at';

    protected $table = 'location_quickbooks_mapping';

    protected $fillable = [
        'dealer_id',
        'dealer_location_id'
    ];

    public function hasBeenSynced(): bool
    {
        return (bool)$this->quickbooks_id;
    }
}
