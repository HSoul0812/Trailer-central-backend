<?php

namespace App\Models\Inventory;

use App\Models\Showroom\Showroom;
use App\Models\Showroom\ShowroomFeature;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class InventoryFeatureList
 * @package App\Models\Inventory
 *
 * @property int $feature_list_id
 * @property string $feature_name
 * @property string $available_options
 * @property string $show_in_only
 *
 * @property ShowroomFeature $pivot
 */
class InventoryFeatureList extends Model
{
    protected $table = 'inventory_feature_list';

    protected $primaryKey = 'feature_list_id';

    public $timestamps = false;

    /**
     * @return BelongsToMany
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Showroom::class, ShowroomFeature::class, 'feature_list_id', 'showroom_id')
            ->using(ShowroomFeature::class)
            ->withPivot('value');
    }
}
