<?php

namespace App\Models\User;

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
     * Get the user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
