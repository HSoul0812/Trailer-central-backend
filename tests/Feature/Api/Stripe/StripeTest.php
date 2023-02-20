<?php

namespace Tests\Feature\Api\Stripe;

use Tests\Common\FeatureTestCase;

class StripeTest extends FeatureTestCase
{
    public function testPlans(): void
    {
        $response = $this->get('/api/stripe/plans');
        $json = json_decode($response->getContent(), true);

        self::assertIsArray($json);
        self::assertNotEmpty($json);

        self::assertArrayHasKey('id', $json[0]);
        self::assertArrayHasKey('name', $json[0]);
        self::assertArrayHasKey('price', $json[0]);
        self::assertArrayHasKey('duration', $json[0]);
        self::assertArrayHasKey('description', $json[0]);
    }
}
