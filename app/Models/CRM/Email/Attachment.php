<?php

namespace App\Models\CRM\Email;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'crm_email_attachments';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'attachment_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message_id',
        'filename',
        'original_filename'
    ];

    /**
     * @var int
     * @var int
     */
    const MAX_FILE_SIZE = 256000000;
    const MAX_UPLOAD_SIZE = 256000000;

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'date_retrieved';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = NULL;

    /**
     * Get message by attachment.
     */
    public function message()
    {
        return $this->belongsTo(Lead::class, "lead_id", "identifier");
    }
}
