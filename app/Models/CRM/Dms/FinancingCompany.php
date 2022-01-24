<?php

namespace App\Models\CRM\Dms;

use App\Helpers\StringHelper;
use App\Utilities\JsonApi\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class FinancingCompany
 *
 * @package App\Models\CRM\Dms
 */
class FinancingCompany extends Model implements Filterable
{
    use SoftDeletes;

    protected $table = 'dms_financing_company';

    protected $fillable = [
        'dealer_id',
        'first_name',
        'last_name',
        'display_name',
        'email',
        'drivers_license',
        'home_phone',
        'work_phone',
        'cell_phone',
        'address',
        'city',
        'region',
        'postal_code',
        'country',
        'tax_exempt',
        'account_number',
        'gender',
        'dob',
        'fin',
    ];

    public function jsonApiFilterableColumns(): ?array
    {
        return ['display_name'];
    }

    /**
     * @param string|null $value
     *
     * @return void
     */
    public function setFirstNameAttribute(?string $value): void
    {
        $this->attributes['first_name'] = StringHelper::trimWhiteSpaces($value);
    }

    /**
     * @param string|null $value
     *
     * @return void
     */
    public function setLastNameAttribute(?string $value): void
    {
        $this->attributes['last_name'] = StringHelper::trimWhiteSpaces($value);
    }

    /**
     * @param string|null $value
     *
     * @return void
     */
    public function setDisplayNameAttribute(?string $value): void
    {
        $this->attributes['display_name'] = StringHelper::trimWhiteSpaces($value);
    }
}
