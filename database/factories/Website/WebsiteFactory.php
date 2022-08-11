<?php

declare(strict_types=1);

/** @var Factory $factory */

use App\Models\User\User;
use App\Models\Website\Website;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

$factory->define(Website::class, static function (Faker $faker, array $attributes): array {
    // Get Dealer ID
    $dealer_id = $attributes['dealer_id'] ?? factory(User::class)->create()->getKey();

    // Get Domain
    $domain = $attributes['domain'] ?? $faker->unique()->uuid . '.' . $faker->tld;
    $template = $attributes['template'] ?? preg_replace('/[.].+$/', '', $domain);

    // Created At
    $createdAt = $faker->dateTimeThisMonth;

    // Return Array
    return [
        'domain' => $domain,
        'canonical_host' => '',
        'render' => 'twig',
        'render_cms' => 0,
        'https_supported' => 1,
        'type' => $attributes['template'] ?? 'website',
        'template' => $template,
        'responsive' => 1,
        'dealer_id' => $dealer_id,
        'type_config' => $attributes['type_config'] ?? '',
        'handling_fee' => '0.00',
        'parts_fulfillment' => 0,
        'date_created' => $createdAt,
        'date_updated' => $createdAt,
        'is_active' => 0,
        'is_live' => 0,
        'parts_email' => '',
        'force_elastic' => 1
    ];
});
