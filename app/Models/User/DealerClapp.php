<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class DealerClapp extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "dealer_clapp";

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "dealer_id",
        "slots",
        "chrome_mode",
        "since"
    ];
}
