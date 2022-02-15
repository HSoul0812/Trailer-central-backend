<?php

namespace App\Models\CRM\User;

use Illuminate\Database\Eloquent\Model;

/**
 * Class EmailFolder
 * @package App\Models\CRM\User
 */
class EmailFolder extends Model
{
    const TABLE_NAME = 'crm_email_folders';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'folder_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sales_person_id',
        'user_id',
        'name',
        'date_added',
        'date_imported',
        'failures',
        'failures_since',
        'deleted',
        'error'
    ];

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'date_added';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = NULL;

    public function salesPerson()
    {
        return $this->belongsTo(SalesPerson::class, 'id', 'sales_person_id');
    }

    public static function getTableName() {
        return self::TABLE_NAME;
    }


    /**
     * Get Default Email Folders
     * 
     * @return Collection of EmailFolder
     */
    public static function getDefaultFolders() {
        // Add Folders to Array
        $defaults = [
            [
                'name' => 'INBOX',
                'folder_id' => -6
            ],
            [
                'name' => 'Inbox',
                'folder_id' => -5
            ],
            [
                'name' => 'SENT',
                'folder_id' => -4
            ],
            [
                'name' => 'Sent Mail',
                'folder_id' => -3
            ],
            [
                'name' => 'Sent Items',
                'folder_id' => -2
            ],
            [
                'name' => 'Sent',
                'folder_id' => -1
            ]
        ];

        // Set Folders Array
        $folders = [];
        foreach($defaults as $default) {
            // Get Access Token
            $folder = new EmailFolder();
            $folder->fill($default);
            $folders[] = $folder;
        }

        // Return Folders
        return collect($folders);
    }


    /**
     * Get Default Email Folders
     * 
     * @return Collection of EmailFolder
     */
    public static function getDefaultGmailFolders() {
        // Add Folders to Array
        $defaults = [
            [
                'name' => 'INBOX',
                'folder_id' => -2
            ],
            [
                'name' => 'SENT',
                'folder_id' => -1
            ]
        ];

        // Set Folders Array
        $folders = [];
        foreach($defaults as $default) {
            // Get Access Token
            $folder = new EmailFolder();
            $folder->fill($default);
            $folders[] = $folder;
        }

        // Return Folders
        return collect($folders);
    }
}
