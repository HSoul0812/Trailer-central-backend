<?php

namespace App\Repositories\CRM\User;

use App\Models\CRM\User\EmailFolder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmailFolderRepository implements EmailFolderRepositoryInterface
{
    /**
     * Create Email Folder
     * 
     * @param array $params
     * @return EmailFolder
     */
    public function create($params) {
        return EmailFolder::create($params);
    }

    /**
     * Delete Email Folder
     * 
     * @param int $id
     * @return true if deleted, false if doesn't exist
     */
    public function delete($id) {
        // Find Email Folder
        $folder = EmailFolder::find($id);
        if(empty($folder->folder_id)) {
            return false;
        }

        // Delete Email Folder
        if($folder->delete()) {
            return true;
        }
        return false;
    }

    /**
     * Update Email Folder
     * 
     * @param array $params
     * @return EmailFolder
     */
    public function update($params) {
        $folder = EmailFolder::findOrFail($params['id']);

        DB::transaction(function() use (&$folder, $params) {
            // Reverse Deleted By Default!
            if(!isset($params['deleted'])) {
                $params['deleted'] = 0;
            }

            // Fill Text Details
            $folder->fill($params)->save();
        });

        return $folder;
    }

    /**
     * Create or Update Email Folder
     * 
     * @param array $params
     * @return EmailFolder
     */
    public function createOrUpdate($params) {
        // Get Folder
        if(isset($params['id'])) {
            $folder = EmailFolder::find($params['id']);
            if(!empty($folder->folder_id)) {
                // Update
                return $this->update($params);
            }
        }

        // Create
        return $this->create($params);
    }

    /**
     * Mark Email Folder as Failed
     * 
     * @param int $folderId
     * @return EmailFolder
     */
    public function markFailed($folderId) {
        // Get Folder
        $folder = EmailFolder::find($folderId);
        if(empty($folder->folder_id)) {
            // Doesn't Exist, so Ignore
            return false;
        }

        // Return Update
        return $this->update([
            'id' => $folderId,
            'failures' => ($folder->failures + 1),
            'failures_since' => ($folder->failures_since + 1),
            'deleted' => $folder->deleted,
            'error' => 1
        ]);
    }

    /**
     * Mark Imported as Current Time
     * 
     * @param int $folderId
     * @return EmailFolder
     */
    public function markImported(int $folderId): EmailFolder {
        return $this->update([
            'id' => $folderId,
            'date_imported' => Carbon::now()
        ]);
    }

    /**
     * find records; similar to findBy()
     * @param array $params
     * @return Collection<EmailFolder>
     */
    public function get($params)
    {
        // Find Email Folder By ID
        return EmailFolder::findOrFail($params['id']);
    }

    /**
     * Get All Email Folders for Sales Person
     *
     * @param array $params
     * @return type
     */
    public function getAll($params) {
        // Get Email Folders By Sales Person
        $folders = EmailFolder::where('sales_person_id', $params['sales_person_id']);

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        return $folders->paginate($params['per_page'])->appends($params);
    }

    /**
     * Delete Multiple Folders for Sales Person
     * 
     * @param int $salesPersonId
     * @param array<int> $excludeIds
     * @return int Number of successfully deleted folders
     */
    public function deleteBulk(int $salesPersonId, array $excludeIds): int
    {
        // Get All Folders to Delete
        return EmailFolder::where('sales_person_id', $salesPersonId)
                          ->whereNotIn('folder_id', $excludeIds)
                          ->delete();
    }
}
