<?php

namespace App\Models\User;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\User\SalesPerson;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\User\AuthToken;

/**
 * Class User
 *
 * This User class is for API users
 *
 * @package App\Models\User
 */
class User extends Model implements Authenticatable
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dealer';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'dealer_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_id',
        'name',
        'email',
        'password'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName() {
        return $this->name;
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier() {
        return $this->dealer_id;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword() {}

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken() {}

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value) {}

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName() {}
    
    public function getAccessTokenAttribute()
    {
        $authToken = AuthToken::where('user_id', $this->dealer_id)->firstOrFail();
        return $authToken->access_token;
    }

    public function crmUser()
    {
        return $this->hasOne(CrmUser::class, 'user_id', 'user_id');
    }

    public function salesPerson()
    {
        return $this->hasOne(SalesPerson::class, 'user_id', 'user_id');
    }
    
    public function leads()
    {
        return $this->hasMany(Lead::class, 'dealer_id', 'dealer_id')->where('is_spam', 0);
    }
    
    public function leadsUnassigned()
    {
        return $this->hasMany(Lead::class, 'dealer_id', 'dealer_id')
                    ->leftJoin(LeadStatus::getTableName(), Lead::getTableName().'.identifier', '=', LeadStatus::getTableName().'.tc_lead_identifier')
                    ->where(Lead::getTableName().'.is_spam', 0)
                    ->where(Lead::getTableName().'.is_archived', 0)
                    ->whereRaw(Lead::getTableName().'.date_submitted > CURDATE() - INTERVAL 30 DAY')
                    ->where(function($query) {
                        $query->where(LeadStatus::getTableName().'.sales_person_id', 0)
                            ->whereNull(LeadStatus::getTableName().'.sales_person_id');
                    })->groupBy(Lead::getTableName().'.identifier');
    }
}
