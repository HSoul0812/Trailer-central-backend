<?php

namespace Tests\Integration\App\Api\UserTracking;

use App\Models\WebsiteUser\WebsiteUser;
use Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\Common\IntegrationTestCase;

class UserTrackingTest extends IntegrationTestCase
{
    const USER_TRACK_ENDPOINT = '/api/user/track';

    public function testItThrowsValidationErrorWithBadRequest()
    {
        $this->postJson(self::USER_TRACK_ENDPOINT)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertSeeText('The visitor id field is required.')
            ->assertSeeText('The url field is required.');
    }

    public function testItCanCreateUserTrackingRecord()
    {
        $visitorId = Str::random();
        $event = $this->faker->word();
        $url = $this->faker->url();
        $meta = [
            'foo' => 'bar',
        ];

        $response = $this
            ->postJson(self::USER_TRACK_ENDPOINT, [
                'visitor_id' => $visitorId,
                'event' => $event,
                'url' => $url,
                'meta' => $meta,
            ])
            ->assertCreated()
            ->assertJsonPath('user_tracking.visitor_id', $visitorId)
            ->assertJsonPath('user_tracking.event', $event)
            ->assertJsonPath('user_tracking.url', $url)
            ->assertJsonPath('user_tracking.page_name', null)
            ->assertJsonPath('user_tracking.meta.foo', 'bar');

        $this->assertNotNull($response->json('user_tracking.id'));
        $this->assertNotNull($response->json('user_tracking.created_at'));
        $this->assertNotNull($response->json('user_tracking.updated_at'));
    }

    public function testItCanCreateUserTrackingWithMetaAsNull()
    {
        $visitorId = Str::random();
        $event = $this->faker->word();
        $url = $this->faker->url();

        $this
            ->postJson(self::USER_TRACK_ENDPOINT, [
                'visitor_id' => $visitorId,
                'event' => $event,
                'url' => $url,
            ])
            ->assertCreated()
            ->assertJsonPath('user_tracking.meta', null);
    }

    public function testItDoesNotAcceptBotRequest()
    {
        $this
            ->postJson(
                uri: self::USER_TRACK_ENDPOINT,
                data: [
                    'visitor_id' => Str::random(),
                    'event' => $this->faker->word(),
                    'url' => $this->faker->url(),
                ],
                headers: [
                    'User-Agent' => 'bot',
                ],
            )
            ->assertOk()
            ->assertJsonPath('data', []);
    }

    public function testItCanAssignWebsiteUserIdFromBearerToken()
    {
        $websiteUser = WebsiteUser::factory()->create();

        $token = auth('api')->tokenById($websiteUser->id);

        $this
            ->postJson(
                uri: self::USER_TRACK_ENDPOINT,
                data: [
                    'visitor_id' => Str::random(),
                    'event' => $this->faker->word(),
                    'url' => $this->faker->url(),
                ],
                headers: [
                    'Authorization' => "Bearer $token",
                ],
            )
            ->assertCreated()
            ->assertJsonPath('user_tracking.website_user_id', $websiteUser->id);
    }

    public function testItAssignWebsiteUserIdAsNullIfTokenIsInvalid()
    {
        $token = Str::random();

        $this
            ->postJson(
                uri: self::USER_TRACK_ENDPOINT,
                data: [
                    'visitor_id' => Str::random(),
                    'event' => $this->faker->word(),
                    'url' => $this->faker->url(),
                ],
                headers: [
                    'Authorization' => "Bearer $token",
                ],
            )
            ->assertCreated()
            ->assertJsonPath('user_tracking.website_user_id', null);
    }

    public function testItCanDetectPageNameFromUrl()
    {
        $visitorId = Str::random();

        $urls = [[
            'url' => 'https://trailertrader.com/trailers-for-sale/watercraft-trailers-for-sale?sort=-createdAt',
            'expected_page_name' => 'TT_PLP',
        ], [
            'url' => 'https://trailertrader.com/new-2023-load-rite-146-v-bunk-boat-trailer--QS9o.html',
            'expected_page_name' => 'TT_PDP',
        ]];

        foreach ($urls as $url) {
            $this
                ->postJson(self::USER_TRACK_ENDPOINT, [
                    'visitor_id' => $visitorId,
                    'url' => $url['url'],
                ])
                ->assertCreated()
                ->assertJsonPath('user_tracking.visitor_id', $visitorId)
                ->assertJsonPath('user_tracking.url', $url['url'])
                ->assertJsonPath('user_tracking.page_name', $url['expected_page_name']);
        }
    }
}
