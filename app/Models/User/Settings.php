<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{ 
    const TABLE_NAME = 'dealer_admin_settings';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_id',
        'setting',
        'setting_value'
    ];

    /**
     * Get Dealer
     * 
     * @return HasOne
     */
    public function dealer() {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }
}