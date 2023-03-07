<?php
/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Dms\Quickbooks\QuickbookApprovalDeleted;
use Faker\Generator as Faker;
use Tests\TestCase;

$factory->define(QuickbookApprovalDeleted::class, function (Faker $faker, array $attributes) {
    $dealer_id = $attributes['dealer_id'] ?? TestCase::getTestDealerId();

    return [
        'dealer_id' => $dealer_id,
        'created_at' => new DateTime(),
        'removed_by' => $dealer_id,
        'deleted_at' => $attributes['deleted_at'] ?? now()->subMinute()
    ];
});
