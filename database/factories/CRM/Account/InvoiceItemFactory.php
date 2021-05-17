<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Account\Invoice;
use App\Models\CRM\Account\InvoiceItem;
use App\Models\CRM\Dms\Quickbooks\Item;
use Faker\Generator as Faker;

$factory->define(InvoiceItem::class, function (Faker $faker, array $attributes) {
    $invoice_id = $attributes['invoice_id'] ?? factory(Invoice::class)->create()->id;
    $item_id = $attributes['item_id'] ?? factory(Item::class)->create()->id;
    $unit_price = $attributes['unit_price'] ?? 0.00;

    return [
        'invoice_id' => $invoice_id,
        'item_id' => $item_id,
        'qty' => 0.00,
        'unit_price' => $unit_price
    ];
});
