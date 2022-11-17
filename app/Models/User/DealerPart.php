<?php

namespace App\Models\User;

use App\Models\Traits\TableAware;
use App\Models\Upload\Upload;
use Illuminate\Database\Eloquent\Model;

class DealerPart extends Model
{
    use TableAware;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dealer_parts';

    /**
     * @var int
     */
    protected $primaryKey = 'dealer_id';

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "dealer_id",
        "since"
    ];

    public function dealer(): BelongsTo
    {
        return $this->hasOne(User::class, 'dealer_id', 'user_id');
    }

}
