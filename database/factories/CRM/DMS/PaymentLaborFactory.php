<?php
declare(strict_types=1);

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\Models\CRM\Dms\PaymentLabor;
use App\Models\CRM\Account\Payment;
use App\Models\CRM\Dms\ServiceOrder\LaborCode;

$factory->define(PaymentLabor::class, function(Faker $faker) {
    return [
        'payment_id' => function () {
            return factory(Payment::class)->create()->id;
        },
        'quantity' => 1,
        'unit_price' => 123.00,
        'dealer_cost' => 456.00,
        'labor_code' => function () {
            return factory(LaborCode::class)->create()->id;
        },
        'status' => 'open',
        'cause' => $faker->title . '_' . rand(10000, 10000000000),
        'actual_hours' => 11.00,
        'paid_hours' => 22.00,
        'billed_hours' => 33.00,
        'technician' => $faker->name . '_' . rand(10000, 10000000000),
        'notes' => $faker->title . '_' . rand(10000, 10000000000),
    ];
});
