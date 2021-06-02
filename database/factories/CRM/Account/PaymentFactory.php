<?php
declare(strict_types=1);

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\Models\CRM\Account\Payment;
use App\Models\CRM\Account\Invoice;

$factory->define(Payment::class, function(Faker $faker){
    return [
        'invoice_id' => function () {
            return factory(Invoice::class)->create()->id;
        },
        'dealer_id' => 9999,
        'amount' => 123.00,
        'date' => $faker->date(),
        'created_at' => $faker->dateTime()
    ];
});
