<?php

namespace Tests\Integration\App\Api\UserTracking;

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
            ->assertSeeText('The event field is required.')
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
}
