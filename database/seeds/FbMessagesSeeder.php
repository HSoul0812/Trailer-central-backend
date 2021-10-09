<?php

use Illuminate\Database\Seeder;

use App\Models\CRM\Interactions\Facebook\Conversation;
use App\Models\CRM\Interactions\Facebook\Message;
use App\Models\Integration\Facebook\Page;
use App\Models\CRM\Leads\Facebook\User as FbUser;
use App\Models\CRM\Leads\Facebook\Lead as FbLead;

class FbMessagesSeeder extends Seeder
{
    // Dealer ID
    const DEALER_ID = 1001;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Page
        $page = factory(Page::class)->create(['dealer_id' => self::DEALER_ID]);

        // Create FB User
        $fbUser = factory(FbUser::class)->create();

        // Create FB Lead
        $fbLead = factory(FbLead::class)->create([
            'page_id' => $page->page_id
        ]);


        // Create Conversation With FB User
        $conversation1 = factory(Conversation::class)->create([
            'page_id' => $page->page_id,
            'user_id' => $fbUser->user_id
        ]);
        $messagesSeed1 = [
            ['conversation_id' => $conversation1->conversation_id, 'from_id' => $conversation1->user_id, 'to_id' => $conversation1->page_id],
            ['conversation_id' => $conversation1->conversation_id, 'to_id' => $conversation1->user_id, 'from_id' => $conversation1->page_id],
            ['conversation_id' => $conversation1->conversation_id, 'from_id' => $conversation1->user_id, 'to_id' => $conversation1->page_id],
            ['conversation_id' => $conversation1->conversation_id, 'from_id' => $conversation1->user_id, 'to_id' => $conversation1->page_id],
            ['conversation_id' => $conversation1->conversation_id, 'to_id' => $conversation1->user_id, 'from_id' => $conversation1->page_id],
            ['conversation_id' => $conversation1->conversation_id, 'to_id' => $conversation1->user_id, 'from_id' => $conversation1->page_id],
            ['conversation_id' => $conversation1->conversation_id, 'to_id' => $conversation1->user_id, 'from_id' => $conversation1->page_id],
            ['conversation_id' => $conversation1->conversation_id, 'from_id' => $conversation1->user_id, 'to_id' => $conversation1->page_id]
        ];
        $messages1 = [];
        foreach($messagesSeed1 as $seed) {
            $messages1[] = factory(Message::class)->create($seed);
        }


        // Create Conversation With FB Lead
        $conversation2 = factory(Conversation::class)->create([
            'page_id' => $page->page_id,
            'user_id' => $fbLead->user_id
        ]);
        $messagesSeed2 = [
            ['conversation_id' => $conversation2->conversation_id, 'from_id' => $conversation2->user_id, 'to_id' => $conversation2->page_id],
            ['conversation_id' => $conversation2->conversation_id, 'from_id' => $conversation2->user_id, 'to_id' => $conversation2->page_id],
            ['conversation_id' => $conversation2->conversation_id, 'to_id' => $conversation2->user_id, 'from_id' => $conversation2->page_id]
        ];
        $messages2 = [];
        foreach($messagesSeed2 as $seed) {
            $messages2[] = factory(Message::class)->create($seed);
        }
    }
}