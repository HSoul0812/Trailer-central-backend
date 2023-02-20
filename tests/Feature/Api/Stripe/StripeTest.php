<?php

namespace Tests\Feature\Api\Stripe;

use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Common\FeatureTestCase;

class StripeTest extends FeatureTestCase
{
    public function testPlans(): void
    {
        $response = $this->get('/api/stripe/plans');
        $json = json_decode($response->getContent(), true);

        self::assertIsArray($json);

        $response->assertJson(fn (AssertableJson $json) =>
            $json->each(fn (AssertableJson $json) =>
                $json->hasAll(['id', 'name', 'price', 'duration', 'description'])
            )
        );
    }
}
