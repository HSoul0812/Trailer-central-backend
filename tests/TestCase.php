<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

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