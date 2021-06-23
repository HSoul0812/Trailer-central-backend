<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

/**
 * Cash Movement model associated with Register
 *
 * A POS register cash movement "session"
 *
 * @package App\Models\Pos
 */
class CashMovement extends Model
{
    const UPDATED_AT = null;

    protected $table = 'crm_pos_cash_movement';

    protected $fillable = ['register_id', 'amount', 'reason'];

    /**
     * Relation to register this cash moment belongs
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function register()
    {
        return $this->belongsTo(Register::class, 'register_id', 'id');
    }
}
