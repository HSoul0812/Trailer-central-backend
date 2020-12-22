<?php

namespace App\Models\CRM\User;

use App\Models\CRM\Dms\UnitSale;
use App\Models\CRM\Interactions\EmailHistory;
use App\Models\CRM\User\EmailFolder;
use App\Models\Pos\Sale;
use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;
use App\Models\Integration\Auth\AccessToken;
use App\Utilities\JsonApi\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SalesPerson
 * @package App\Models\CRM\User
 * @property Collection<Sale> $posSales
 * @property Collection<GenericSaleInterface> $allSales
 */
class SalesPerson extends Model implements Filterable
{
    use SoftDeletes;

    const TABLE_NAME = 'crm_sales_person';

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
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'dealer_location_id',
        'perms',
        'first_name',
        'last_name',
        'email',
        'is_default',
        'is_inventory',
        'is_financing',
        'is_trade',
        'signature',
        'dealer_location_id',
        'smtp_email',
        'smtp_password',
        'smtp_server',
        'smtp_port',
        'smtp_security',
        'smtp_auth',
        'smtp_failed',
        'smtp_error',
        'imap_email',
        'imap_password',
        'imap_server',
        'imap_port',
        'imap_security',
        'imap_failed'
    ];

    /**
     * Disable timestamps
     *
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * Define Type Arrays
     *
     * @var array
     */
    const TYPES_DEFAULT   = ['general', 'manual'];
    const TYPES_INVENTORY = ['craigslist', 'inventory', 'call'];
    const TYPES_VALID     = ['default', 'inventory', 'financing', 'trade'];


    /**
     * Get the sales person's full name
     * 
     * @return string
     */
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function dealer()
    {
        return $this->hasOne(Dealer::class, 'user_id', 'user_id');
    }

    public function crmUser()
    {
        return $this->hasOne(CrmUser::class, 'user_id', 'user_id');
    }

    /**
     * Get new dealer user
     */
    public function newDealerUser()
    {
        return $this->belongsTo(NewDealerUser::class, 'user_id', 'user_id');
    }

    public function posSales()
    {
        return $this->hasMany(Sale::class, 'sales_person_id');
    }

    public function unitSales()
    {
        return $this->hasMany(UnitSale::class, 'sales_person_id');
    }

    public function folders()
    {
        return $this->hasMany(EmailFolder::class, 'sales_person_id')->where('deleted', 0);
    }

    /**
     * Google Access Token
     * 
     * @return HasOne
     */
    public function googleToken()
    {
        return $this->hasOne(AccessToken::class, 'relation_id', 'id')
                    ->whereTokenType('google')
                    ->whereRelationType('sales_person');
    }

    /**
     * Get From Email History
     * 
     * @return HasMany
     */
    public function fromEmails() {
        return $this->hasMany(EmailHistory::class, 'from_email', 'email')
                    ->orWhere(SalesPerson::getTableName() . '.smtp_email', '=', EmailHistory::getTableName() . '.from_email');
        
    }

    /**
     * @return Collection<GenericSaleInterface>
     */
    public function allSales() {
        return $this->posSales->merge($this->unitSales);
    }

    public function jsonApiFilterableColumns(): ?array
    {
        return ['*'];
    }


    /**
     * Get Email Folders Including Defaults
     * 
     * @return Collection of EmailFolder
     */
    public function getEmailFoldersAttribute() {
        // Get Email Folders Based on Existing Data
        if(!empty($this->folders)) {
            return $this->folders;
        }

        // Google Token Exists?
        if(!empty($this->googleToken)) {
            // Return Only Google Defaults
            return $this->getDefaultGmailFolders();
        }

        // Return Default Folders
        return $this->getDefaultFolders();
    }

    public static function getTableName() {
        return self::TABLE_NAME;
    }


    /**
     * Get Default Email Folders
     * 
     * @return Collection of EmailFolder
     */
    private function getDefaultFolders() {
        // Add Folders to Array
        $defaults = array(
            array(
                'name' => 'INBOX',
                'folder_id' => -4
            ),
            array(
                'name' => 'INBOX.Sent Mail',
                'folder_id' => -3
            ),
            array(
                'name' => '[Gmail]/Sent Mail',
                'folder_id' => -2
            ),
            array(
                'name' => 'Sent Items',
                'folder_id' => -1
            )
        );

        // Set Folders Array
        $folders = array();
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
    private function getDefaultGmailFolders() {
        // Add Folders to Array
        $defaults = array(
            array(
                'name' => 'INBOX',
                'folder_id' => -4
            ),
            array(
                'name' => 'SENT',
                'folder_id' => -3
            )
        );

        // Set Folders Array
        $folders = array();
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
