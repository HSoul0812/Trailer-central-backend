<?php

namespace App\Models\User;

use App\Models\Interactions\DealerLocation;
use Illuminate\Database\Eloquent\Model;

class Dealer extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "new_dealer_user";

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Get the user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function location()
    {
        return $this->hasOne(DealerLocation::class, 'dealer_id', 'id');
    }
}
