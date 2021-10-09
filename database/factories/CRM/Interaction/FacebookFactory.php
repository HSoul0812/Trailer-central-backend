<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\CRM\Interactions\Facebook\Conversation;
use App\Models\CRM\Interactions\Facebook\Message;
use App\Models\CRM\Leads\Facebook\User as FbUser;
use App\Models\Integration\Facebook\Page;
use App\Models\User\User;
use Faker\Generator as Faker;

$factory->define(Conversation::class, function (Faker $faker, array $attributes) {
    // Get Page Id
    if(!empty($attributes['page_id'])) {
        $page_id = $attributes['page_id'];
    } else {
        // Get Dealer ID
        $dealer_id = $attributes['dealer_id'] ?? factory(User::class)->create()->getKey();

        // Get Page
        $page_id = factory(Page::class)->create([
            'dealer_id' => $dealer_id
        ])->page_id;
    }

    // Get User
    $user_id = $attributes['user_id'] ?? factory(FbUser::class)->create()->getKey();

    // Return Overrides
    return [
        'conversation_id' => 't_' . $faker->randomNumber(20, true),
        'page_id' => $page_id,
        'user_id' => $user_id,
        'link' => '/' . $page_id . '/inbox/' . $faker->randomNumber(16, true) . '/',
        'snippet' => $faker->sentence,
        'newest_update' => $faker->dateTimeThisMonth->format('Y-m-d H:i:s')
    ];
});

$factory->define(Message::class, function (Faker $faker, array $attributes) {
    // Get Conversation
    $conversation = $attributes['conversation_id'] ?? factory(Conversation::class)->create();
    $conversation_id = $conversation->getKey();

    // Get Page/User
    $page_id = $conversation->page_id;
    $user_id = $conversation->user_id;

    // Return Overrides
    return [
        'message_id' => 'm_' . $faker->randomNumber(90, true),
        'conversation_id' => $conversation_id,
        'interaction_id' => 0,
        'from_id' => $attributes['from_id'] ?? $user_id,
        'to_id' => $attributes['to_id'] ?? $page_id,
        'message' => $faker->sentence,
        'tags' => '',
        'read' => 0
    ];
});