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
class Register extends Model
{
    protected $table = 'crm_pos_register';

    protected $fillable = ['outlet_id', 'floating_amount', 'open_notes'];

    public $timestamps = false;
}
