<?php

namespace App\Repositories\CRM\User;

use App\Repositories\Repository;

interface EmailFolderRepositoryInterface extends Repository {
    /**
     * Create or Update Email Folder
     * 
     * @param array $params
     * @return EmailFolder
     */
    public function createOrUpdate($params);

    /**
     * Mark Email Folder as Failed
     * 
     * @param int $folderId
     * @return EmailFolder
     */
    public function markFailed($folderId);
}
