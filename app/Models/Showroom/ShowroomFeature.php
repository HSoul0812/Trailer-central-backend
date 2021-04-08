<?php

namespace App\Models\Showroom;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class ShowroomFeature
 * @package App\Models\Showroom
 *
 * @property int $showroom_feature_id
 * @property int $showroom_id
 * @property int $feature_list_id
 * @property string $value
 */
class ShowroomFeature extends Pivot
{
    protected $table = 'showroom_feature';

    protected $primaryKey = 'showroom_feature_id';

    public $timestamps = false;
}
