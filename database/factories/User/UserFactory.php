<?php

declare(strict_types=1);

/** @var Factory $factory */

use App\Models\User\User;
use App\Services\Common\EncrypterServiceInterface;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

$factory->define(User::class, static function (Faker $faker, array $attributes): array {

    $salt = $faker->md5;

    $encrypter = app()->make(EncrypterServiceInterface::class);

    $createdAt = $faker->dateTimeThisMonth;

    return [
        'name' => $attributes['name'] ?? $faker->name(),
        'email' => $attributes['email'] ?? $faker->companyEmail,
        'salt' => $salt,
        'password' => $encrypter->encryptBySalt($attributes['password'] ?? $faker->password(), $salt),
        'type' => $attributes['type'] ?? $faker->randomElement(User::TYPES),
        'state' => $attributes['state'] ?? $faker->randomElement(User::STATUSES),
        'created_at' => $createdAt,
        'updated_at' => $createdAt
    ];
});
