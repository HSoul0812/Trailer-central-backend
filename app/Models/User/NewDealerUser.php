<?php

namespace App\Models\User;

use App\Models\User\DealerLocation;
use App\Models\Upload\Upload;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use Illuminate\Database\Eloquent\Model;

class NewDealerUser extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "new_dealer_user";

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
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the crm user
     */
    public function crmUser()
    {
        return $this->belongsTo(CrmUser::class, 'user_id', 'user_id');
    }

    public function location()
    {
        return $this->hasOne(DealerLocation::class, 'dealer_id', 'dealer_id');
    }

    public function uploads() {
        return $this->hasMany(Upload::class, 'dealer_upload', 'dealer_id');
    }

    /**
     * @return type
     */
    public function leadsUnassigned()
    {
        $hasMany = $this->hasManyThrough(LeadStatus::class, Lead::class, 'dealer_id', 'tc_lead_identifier', 'id', 'identifier')
                    ->where(Lead::getTableName().'.is_spam', 0)
                    ->where(Lead::getTableName().'.is_archived', 0)
                    ->whereRaw(Lead::getTableName().'.date_submitted > CURDATE() - INTERVAL 30 DAY')
                    ->where(function($query) {
                        $query->where(LeadStatus::getTableName().'.sales_person_id', 0)
                            ->whereNull(LeadStatus::getTableName().'.sales_person_id');
                    })->groupBy(Lead::getTableName().'.identifier');
        var_dump($hasMany);
        die;
    }
}
