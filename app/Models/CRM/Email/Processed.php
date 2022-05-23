<?php

namespace App\Models\CRM\Email;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

class Processed extends Model
{
    use TableAware;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'crm_email_processed';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'message_id';
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message_id',
        'user_id',
        'date_processed'
    ];

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'date_processed';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = NULL;

    /**
     * Get new dealer user
     */
    public function newDealerUser()
    {
        return $this->belongsTo(NewDealerUser::class, 'user_id', 'user_id');
    }
}
