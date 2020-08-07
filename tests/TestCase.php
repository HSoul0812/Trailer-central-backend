<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        $this->reset();

        parent::tearDown();
    }

    protected function accessToken()
    {
        return env('TESTS_DEFAULT_ACCESS_TOKEN', '123');
    }
}