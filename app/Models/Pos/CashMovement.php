<?php


namespace App\Models\Pos;


use Illuminate\Database\Eloquent\Model;

/**
 * Class Register
 *
 * A POS register "session"
 *
 * @package App\Models\Pos
 */
class CashMovement extends Model
{
    const UPDATED_AT = null;

    protected $table = 'crm_pos_cash_movement';

    protected $fillable = ['register_id', 'amount', 'reason'];
}
