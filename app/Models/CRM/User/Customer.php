<?php

namespace App\Models\CRM\User;

use ElasticScoutDriverPlus\CustomSearch;
use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\Dms\UnitSale;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Searchable;

class Customer extends Model
{
    use Searchable, CustomSearch;

    protected $table = 'dms_customer';

    public $timestamps = false;

    protected $fillable = [
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
        'website_lead_id',
        'tax_exempt',
        'is_financing_company',
        'account_number',
        'gender',
        'dob',
        'deleted_at',
        'is_wholesale',
        'default_discount_percent',
        'middle_name',
        'company_name',
        'use_same_address',
        'shipping_address',
        'shipping_city',
        'shipping_region',
        'shipping_postal_code',
        'shipping_country',
        'county',
        'shipping_county',
    ];

    public function quotes()
    {
        return $this->hasMany(UnitSale::class, 'buyer_id', 'id');
    }

    public function openQuotes()
    {
        return $this->quotes()->where('is_archived', 0)
            ->where(function($query) {
                $query->whereDoesntHave('payments')
                    ->orWhereHas('payments', function($query) {
                        $query->select(DB::raw('sum(amount) as paid_amount'))
                            ->groupBy('invoice_id')
                            ->havingRaw('paid_amount < dms_unit_sale.total_price or paid_amount <= 0');
                    });
            });

    }

    public function searchableAs()
    {
        return 'customers';
    }

    public function toSearchableArray()
    {
        $array = $this->toArray();

        $array['dealer_id'] = (int)$array['dealer_id'];
        $array['tax_exempt'] = (int)$array['tax_exempt'];
        $array['is_wholesale'] = (int)$array['is_wholesale'];

        return $array;
    }
}
