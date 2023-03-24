<?php

namespace App\Models\Showroom;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class ShowroomGenericMap
 * @package App\Models\Showroom
 *
 * @property int $id
 * @property string $external_mfg_key
 * @property int $showroom_id
 * @property string|null $true_model
 *
 * @property Collection<Showroom> $showrooms
 */
class ShowroomGenericMap extends Model
{
    protected $table = 'showroom_generic_map';

    /**
     * @return HasMany
     */
    public function showrooms(): HasMany
    {
        return $this->hasMany(Showroom::class, 'id', 'showroom_id');
    }
}
