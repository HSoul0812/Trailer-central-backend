<?php

namespace App\Models\CRM\Interactions;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\TableAware;

class InteractionEmail extends Model
{
    use TableAware;

    const TABLE_NAME = 'crm_interaction_emails';

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
    protected $primaryKey = 'interaction_id';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'date_added';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = NULL;
        
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'interaction_id',
        'message_id'
    ];
}