<?php

namespace App\Models\CRM\Email;

use Illuminate\Database\Eloquent\Model;

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
     * Get message by attachment.
     */
    public function message()
    {
        return $this->belongsTo(Lead::class, "lead_id", "identifier");
    }
}
