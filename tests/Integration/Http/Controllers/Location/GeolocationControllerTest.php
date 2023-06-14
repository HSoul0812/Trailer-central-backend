<?php

namespace Tests\Integration\Http\Controllers\Location;

use App\Models\User\Location\Geolocation;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @group DW
 * @group DW_GEOLOCATION
 */
class GeolocationControllerTest extends TestCase
{
    public function testItRequiresAValidAccessTokenToSearch()
    {
        $response = $this->getJson('/api/geolocation/search', [
            'access-token' => Str::random(8)
        ]);

        $response->assertForbidden();
    }

    public function testItCanSearchByKnownParams()
    {
        $geolocation = factory(Geolocation::class)->create([
            'zip' => 'somezip'
        ]);
        $response = $this->getJson('/api/geolocation/search?zip=somezip', [
            'access-token' => $this->accessToken()
        ]);

        $response->assertOk();
        $this->assertSame($response->json('data.0.id'), $geolocation->id);
        $this->assertSame($response->json('data.0.zip'), $geolocation->zip);

        $geolocation->delete();
    }
}
