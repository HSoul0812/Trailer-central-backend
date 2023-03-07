<?php
namespace App\Models\Parts\Textrail;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\TableAware;

/**
 * @property int $id
 * @property int $attribute_id
 * @property int $part_id
 * @property string $attribute_value
 */
class PartAttribute extends Model
{
    use TableAware;

    protected $table = 'textrail_part_attributes';

    protected $fillable = [
        'attribute_id',
        'part_id',
        'attribute_value',
    ];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id', 'id');
    }
}
