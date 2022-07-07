<?php

namespace App\Models\User;

use App\Models\User\DealerLocation;
use App\Models\Upload\Upload;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadType;
use App\Models\CRM\User\SalesPerson;
use App\Models\Website\Website;
use Illuminate\Database\Eloquent\Model;
use App\Models\User\NewUser;
use Illuminate\Database\Eloquent\Relations\HasOne;

class NewDealerUser extends Model
{
    const TABLE_NAME = 'new_dealer_user';


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'default_description',
        'use_description_in_feed',
        'auto_website',
        'salt',
        'type',
        'stripe_id',
        'stripe_response',
        'state',
        'showroom',
        'showroom_dealers',
        'import_config',
        'auto_import_hide',
        'auto_msrp',
        'auto_msrp_percent',
        'autoresponder_enable',
        'autoresponder_text',
        'is_utc_inactive',
        'feature_parts',
        'deleted',
        'newsletter_enabled',
        'crm_login',
        'parts_payout_id'
    ];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Get the user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'dealer_id');
    }

    /**
     * @return Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function website()
    {
        return $this->hasOne(Website::class, 'dealer_id', 'id');
    }

    /**
     * Get the crm user
     */
    public function crmUser()
    {
        return $this->belongsTo(CrmUser::class, 'user_id', 'user_id');
    }

    /**
     * Get active crm user
     */
    public function activeCrmUser()
    {
        return $this->crmUser()->where('active', 1);
    }

    /**
     * Get dealer locations
     */
    public function location()
    {
        return $this->hasMany(DealerLocation::class, 'dealer_id', 'id');
    }
    
    public function newUser() : HasOne 
    {
        return $this->hasOne(NewUser::class, 'user_id', 'user_id');
    }

    /**
     * Get uploads
     */
    public function uploads() {
        return $this->hasMany(Upload::class, 'dealer_upload', 'dealer_id');
    }


    /**
     * Get Salespeople
     * 
     * @return HasMany
     */
    public function salespeople() {
        return $this->hasMany(SalesPerson::class, 'user_id', 'user_id');
    }

    /**
     * Get Salespeople w/Emails
     * 
     * @return HasMany
     */
    public function salespeopleEmails() {
        return $this->salespeople()->whereNotNull('email')->where('email', '<>', '')->orderBy('id', 'asc');
    }

    /**
     * Get leads
     * 
     * @return HasMany
     */
    public function leads()
    {
        return $this->hasMany(Lead::class, 'dealer_id', 'id')->where('is_spam', 0)
                    ->where('lead_type', '<>', LeadType::TYPE_NONLEAD);
    }
    
    public static function getTableName() {
        return self::TABLE_NAME;
    }
}
