<?php

declare(strict_types=1);

namespace Tests\Common;

use App\Models\WebsiteUser\WebsiteUser;

abstract class FeatureTestCase extends TestCase
{
    public function jwtAuthToken(): string
    {
        $websiteUser = WebsiteUser::factory()->create();

        return auth('api')->login($websiteUser);
    }
}
