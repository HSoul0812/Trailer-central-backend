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
     * Get message by attachment.
     */
    public function message()
    {
        return $this->belongsTo(Lead::class, "lead_id", "identifier");
    }

    /**
     * @param $files - mail attachment(-s)
     * @return bool | string
     */
    public function checkAttachmentsSize($files)
    {
        $totalSize = 0;
        foreach ($files as $file) {
            if ($file['size'] > 2097152) {
                throw new Exception("Single upload size must be less than 2 MB.");
            } else if ($totalSize > 8388608) {
                throw new Exception("Total upload size must be less than 8 MB");
            }
            $totalSize += $file['size'];
        }

        return true;
    }

    public function uploadAttachments($files, $dealer, $uniqueId) {
        $messageDir = str_replace(">", "", str_replace("<", "", $uniqueId));

        if (!empty($files) && is_array($files)) {
            $message = $this->checkAttachmentsSize($files);
            if( false !== $message ) {
                return response()->json([
                    'error' => true,
                    'message' => $message
                ], Response::HTTP_BAD_REQUEST);
            }
            foreach ($files as $file) {
                $path_parts = pathinfo( $file->getPathname() );
                $filePath = 'https://email-trailercentral.s3.amazonaws.com/' . 'crm/'
                    . $dealer->id . "/" . $messageDir
                    . "/attachments/{$path_parts['filename']}." . $path_parts['extension'];
                Storage::disk('s3')->put($filePath, file_get_contents($file));
                Attachment::create(['message_id' => $uniqueId, 'filename' => $filePath, 'original_filename' => time() . $file->getClientOriginalName()]);
            }
        }
    }
}
