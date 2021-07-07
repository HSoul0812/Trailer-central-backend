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
use App\Traits\SmtpHelper;
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
    use SoftDeletes, SmtpHelper;

    /**
     * @const string
     */
    const TYPE_SMTP = 'smtp';

    /**
     * @const string
     */
    const TYPE_IMAP = 'imap';


    const TABLE_NAME = 'crm_sales_person';

    /**
     * @const array of currently supported auth types for email
     */
    const AUTH_TYPES = [
        'google' => 'Gmail (OAuth 2)',
        'office365' => 'Office 365 (OAuth 2)',
        'ntlm' => 'MS Exchange (SMTP/IMAP)',
        'custom' => 'Custom (SMTP/IMAP)'
    ];

    /**
     * @const array of currently supported auth types for email
     */
    const AUTH_TYPE_METHODS = [
        'google' => 'oauth',
        'office365' => 'oauth',
        'ntlm' => 'smtp',
        'custom' => 'smtp'
    ];
    const AUTH_METHOD_NTLM = 'ntlm';
    const AUTH_METHOD_CUSTOM = 'custom';

    /**
     * @const array custom smtp auth type map from key => name
     */
    const CUSTOM_AUTH = [
        'auto' => 'Auto Detect',
        'PLAIN' => 'PLAIN',
        'LOGIN' => 'LOGIN'
    ];

    /**
     * @const array ntlm smtp auth type map from key => name
     */
    const NTLM_AUTH = [
        'NTLM' => 'MS Exchange'
    ];

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
     * Access Tokens
     * 
     * @return HasMany
     */
    public function tokens()
    {
        return $this->hasMany(AccessToken::class, 'relation_id', 'id')
                    ->whereRelationType('sales_person');
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
        if(!empty($this->folders) && count($this->folders) > 0) {
            return $this->folders;
        }

        // Google Token Exists?
        if(!empty($this->googleToken)) {
            // Return Only Google Defaults
            return EmailFolder::getDefaultGmailFolders();
        }

        // Return Default Folders
        return EmailFolder::getDefaultFolders();
    }


    /**
     * Return Auth Config Type
     * 
     * @return string
     */
    public function getAuthConfigAttribute(): string {
        // Access Token Exists?
        if(!empty($this->tokens)) {
            $token = $this->tokens()->orderBy('issued_at', 'desc')->first();
            if(!empty($token->token_type)) {
                return $token->token_type;
            }
        }

        // Return Auth Config
        if($this->smtp_auth === strtoupper(self::AUTH_METHOD_NTLM)) {
            return self::AUTH_METHOD_NTLM;
        }

        // Return Custom
        return self::AUTH_METHOD_CUSTOM;
    }

    /**
     * Return Auth Types Array Map
     * 
     * @return array<array{label: string, method: string, auth: array}>
     */
    public function getAuthTypesAttribute(): array {
        // Loop Auth Types
        $authTypes = [];
        foreach(self::AUTH_TYPES as $type => $label) {
            // Get Method
            $method = self::AUTH_TYPE_METHODS[$type];

            // Get Auth Types
            $auth = [];
            if($type === self::AUTH_METHOD_NTLM) {
                $auth = self::NTLM_AUTH;
            } elseif($type === self::AUTH_METHOD_CUSTOM) {
                $auth = self::CUSTOM_AUTH;
            }

            // Append Auth Types
            $authTypes[$type] = [
                'label' => $label,
                'method' => $method,
                'auth' => $auth
            ];
        }

        // Return Auth Types
        return $authTypes;
    }

    /**
     * Validate SMTP Details Using Swift Transport
     * 
     * @return bool
     */
    public function getSmtpValidateAttribute(): bool {
        return $this->validateSalesPersonSmtp($this);
    }

    /**
     * Validate IMAP Details
     * TO DO: Validate from here like SMTP above!
     * 
     * @return bool
     */
    public function getImapValidateAttribute(): bool {
        return $this->imap_failed;
    }

    public static function getTableName() {
        return self::TABLE_NAME;
    }
}
