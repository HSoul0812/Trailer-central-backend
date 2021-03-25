<?php

namespace App\Repositories\Traits;

use Illuminate\Support\Collection;

trait EmailAttachmentTrait {

    /**
     * Apply Attachment to Mailable
     * 
     * @param type $build
     * @param \App\Repositories\Traits\Collection $attachments
     */
    public function applyAttachments($build, Collection $attachments)
    {
        var_dump($build);
        die;

        // Attachments Exist Before Looping
        if ($attachments->count() > 0) {
            foreach ($attachments as $attachment) {
                $build->attach($attach['path'], [
                    'as'    => $attach['as'],
                    'mime'  => $attach['mime']
                ]);
            }
        }
    }

}
