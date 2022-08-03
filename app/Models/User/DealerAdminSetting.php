<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class DealerAdminSetting extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dealer_admin_settings';

    /**
     * @var int
     */
    protected $primaryKey = 'id';

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "dealer_id",
        "setting",
        "setting_value"
    ];

    /**
     * Get dealer information
     * @return BelongsTo
     */
    public function dealer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }

}
