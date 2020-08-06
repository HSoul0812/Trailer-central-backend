<?php


namespace App\Models\CRM\Dms;


use App\Utilities\JsonApi\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    ];

    public function jsonApiFilterableColumns(): ?array
    {
        return ['display_name'];
    }
}
