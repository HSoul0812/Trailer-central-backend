<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Account\Invoice;
use App\Models\CRM\Account\InvoiceItem;
use App\Models\CRM\Dms\Quickbooks\Item;
use Faker\Generator as Faker;

$factory->define(InvoiceItem::class, function (Faker $faker) {
    return [
        'invoice_id' => function () {
            return factory(Invoice::class)->create()->id;
        },
        'item_id' => function () {
            return factory(Item::class)->create()->id;
        },
        'qty' => 0.00,
        'unit_price' => 0.00
    ];
});
