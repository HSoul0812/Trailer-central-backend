<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\User\SalesPerson;
use App\Models\CRM\Leads\Lead;

/**
 * Class User
 *
 * This User class is for API users
 *
 * @package App\Models\User
 */
class User extends Model
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

    public function dealer()
    {
        return $this->hasOne(Dealer::class, 'user_id', 'user_id');
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
}
