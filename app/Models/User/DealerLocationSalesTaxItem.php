<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class DealerLocationSalesTaxItem extends Model
{

    protected $table = 'dealer_location_sales_tax_item_v2';

    const TYPE_STATE = 'state';
    const TYPE_COUNTY = 'county';
    const TYPE_CITY = 'city';
    const TYPE_DISTRICT1 = 'district1';
    const TYPE_DISTRICT2 = 'district2';
    const TYPE_DISTRICT3 = 'district3';
    const TYPE_DISTRICT4 = 'district4';
    const TYPE_DMV = 'dmv';
    const TYPE_REGISTRATION = 'registration';

    public static $types = [
        self::TYPE_STATE => 'State',
        self::TYPE_COUNTY => 'County',
        self::TYPE_CITY => 'City',
        self::TYPE_DISTRICT1 => 'District1',
        self::TYPE_DISTRICT2 => 'District2',
        self::TYPE_DISTRICT3 => 'District3',
        self::TYPE_DISTRICT4 => 'District4',
        self::TYPE_DMV => 'Dmv',
        self::TYPE_REGISTRATION => 'Registration',
    ];

}