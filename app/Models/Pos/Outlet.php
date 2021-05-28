<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Outlet
 *
 * A POS outlet "session"
 *
 * @package App\Models\Pos
 */
class Outlet extends Model
{
    protected $table = 'crm_pos_outlet';

    public $timestamps = false;

    public function registers(): HasMany
    {
        return $this->hasMany(Register::class);
    }
}
