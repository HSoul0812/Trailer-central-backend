<?php

namespace App\Models\CRM\Email;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Email Blast
 *
 * @package App\Models\CRM\Email
 */
class Blast extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'crm_email_bounces';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'email_bounce_id';

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
        'email_address',
        'type'
    ];
}