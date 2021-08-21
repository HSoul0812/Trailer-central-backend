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

    /**
     * Mark Imported as Current Time
     * 
     * @param int $folderId
     * @return EmailFolder
     */
    public function markImported(int $folderId): EmailFolder;

    /**
     * Delete Multiple Folders for Sales Person
     * 
     * @param int $salesPersonId
     * @param array<int> $excludeIds
     * @return int Number of successfully deleted folders
     */
    public function deleteBulk(int $salesPersonId, array $excludeIds = []): int;
}
