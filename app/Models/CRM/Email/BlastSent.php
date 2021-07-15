<?php

namespace App\Models\CRM\Email;

use App\Models\Traits\Inventory\CompositePrimaryKeys;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Email Blast Sent
 *
 * @package App\Models\CRM\Email
 */
class BlastSent extends Model
{
    use TableAware, CompositePrimaryKeys;

    const TABLE_NAME = 'crm_email_blasts_sent';

    protected $table = self::TABLE_NAME;

    /**
     * Composite Primary Key
     * 
     * @var array<string>
     */
    protected $primaryKey = ['email_blasts_id', 'lead_id'];

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
        'email_blasts_id',
        'lead_id',
        'message_id'
    ];
}