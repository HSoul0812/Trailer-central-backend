<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    // Initialize Test Constants
    const TEST_DEALER_ID = 1001;
    const TEST_LOCATION_ID = [11998, 12084, 14427];
    const TEST_WEBSITE_ID = [500, 779];

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->reset();

        parent::tearDown();
    }

    protected function accessToken()
    {
        return env('TESTS_DEFAULT_ACCESS_TOKEN', '123');
    }
}