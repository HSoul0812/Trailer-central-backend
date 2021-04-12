<?php

namespace App\Models\Showroom;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ShowroomFieldsMapping
 * @package App\Models\Showroom
 *
 * @property int $id
 * @property string $type
 * @property string $map_from
 * @property string $map_to
 * @property string $field_type
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 */
class ShowroomFieldsMapping extends Model
{
    const TYPE_INVENTORY = 'inventory';
    const TYPE_ATTRIBUTE = 'attribute';
    const TYPE_MEASURE = 'measure';
    const TYPE_IMAGE = 'image';

    const FIELD_TYPE_BOOLEAN = 'boolean';
    const FIELD_TYPE_INTEGER = 'integer';

    protected $table = 'showroom_fields_mapping';
}
