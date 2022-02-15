<?php

namespace App\Repositories\Traits;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Collection;

trait MailableAttachmentTrait {
    /**
     * Apply Attachment to Mailable
     * 
     * @param Mailable $build
     * @param Collection $attachments
     */
    public function applyAttachments(Mailable $build, Collection $attachments)
    {
        // Attachments Exist Before Looping
        if ($attachments->isNotEmpty() > 0) {
            foreach ($attachments as $attachment) {
                // Check Temp File
                $path = !empty($attachment->getTmpName()) ? $attachment->getTmpName() : $attachment->getFilePath();

                // Attach Email
                $build->attach($path, [
                    'as'    => $attachment->getFileName(),
                    'mime'  => $attachment->getMimeType()
                ]);
            }
        }
    }

}
