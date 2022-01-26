<?php

namespace App\Models\Parts;

use App\Helpers\StringHelper;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    protected $table = 'qb_vendors';

    use TableAware, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_id',
        'name',
        'business_email',
        'business_phone',
        'ein',
        'street',
        'city',
        'state',
        'zip_code',
        'country',
        'contact_name',
        'contact_phone',
        'contact_email',
        'terms',
        'account_no',
        'notes',
        'ap_account',
        'active',
        'auto_created',
        'created_at',
        'updated_at',
        'qb_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function parts()
    {
        return $this->hasMany('App\Models\Parts\Part');
    }

    /**
     * @param string|null $value
     *
     * @return void
     */
    public function setContactNameAttribute(?string $value): void
    {
        $this->attributes['contact_name'] = StringHelper::trimWhiteSpaces($value);
    }

    /**
     * @param string|null $value
     *
     * @return void
     */
    public function setNameAttribute(?string $value): void
    {
        $this->attributes['name'] = StringHelper::trimWhiteSpaces($value);
    }
}
