<?php


namespace App\Models\CRM\Interactions;

use App\Models\CRM\Leads\Lead;
use Illuminate\Database\Eloquent\Model;

class TextLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dealer_texts_log';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'date_sent';

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
        'lead_id',
        'log_message',
        'from_number',
        'to_number'
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'identifier', 'lead_id');
    }
}
