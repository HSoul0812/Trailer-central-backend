<?php

namespace App\Models\Integration\Collector;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CollectorFields
 * @package App\Models\Integration\Collector
 *
 * @property int $id
 * @property string $field
 * @property string $label
 * @property string $type
 * @property bool $boolean
 * @property bool $mapped
 */
class CollectorFields extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'collector_fields';
}
