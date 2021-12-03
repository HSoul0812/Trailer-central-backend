<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CRM\Interactions\InteractionMessage;
use Faker\Generator as Faker;

$factory->define(InteractionMessage::class, function (Faker $faker, array $attributes) {
    return [
        'message_type' => $attributes['message_type'],
        'tb_primary_id' => $attributes['tb_primary_id'],
        'tb_name' => $attributes['tb_name'],
        'hidden' => $attributes['hidden'] ?? 0,
        'is_read' => $attributes['is_read'] ?? 0,
    ];
});
