<?php

namespace App\Transformers\CRM\User;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\User\EmailFolder;

class EmailFolderTransformer extends TransformerAbstract
{
    public function transform(EmailFolder $folder)
    {
        return [
            'folder_id' => $folder->id,
            'sales_person_id' => $folder->sales_person_id,
            'user_id' => $folder->user_id,
            'name' => $folder->name,
            'date_added' => $folder->date_added,
            'date_imported' => $folder->date_imported,
            'failures' => $folder->failures_since,
            'error' => $folder->error,
            'deleted' => $folder->deleted
        ];
    }
}
