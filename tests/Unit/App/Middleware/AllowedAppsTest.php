<?php

namespace Tests\Unit\App\Middleware;

use App\Http\Middleware\AllowedApps;
use App\Models\AppToken;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\Common\TestCase;

class AllowedAppsTest extends TestCase
{
    public function testItReturnsErrorWhenNoAppTokenProvided(): void
    {
        $response = (new AllowedApps())->handle(new Request(), fn () => null);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->status());
        $this->assertEquals(
            expected: "Please provide 'app-token' in query param or request body, or a bearer token.",
            actual: data_get($response->getOriginalContent(), 'message')
        );
    }

    public function testItReturnsErrorWhenAppTokenIsInvalid(): void
    {
        $request = new Request();

        $request->headers->set('Authorization', 'Bearer 1234567890');

        $response = (new AllowedApps())->handle($request, fn () => null);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->status());
        $this->assertEquals(
            expected: 'Invalid App Token: 1234567890.',
            actual: data_get($response->getOriginalContent(), 'message')
        );
    }

    public function testItLetRequestGoThroughWhenSetValidAppTokenAsQueryParam(): void
    {
        $appToken = AppToken::factory()->create();

        $request = new Request();

        $request->query->set('app-token', $appToken->token);

        $response = (new AllowedApps())->handle($request, fn () => response()->json(['message' => 'Success!']));

        $this->assertEquals(Response::HTTP_OK, $response->status());
        $this->assertEquals(
            expected: 'Success!',
            actual: data_get($response->getOriginalContent(), 'message')
        );
    }

    public function testItLetRequestGoThroughWhenSetValidAppTokenAsBearerToken(): void
    {
        $appToken = AppToken::factory()->create();

        $request = new Request();

        $request->headers->set('Authorization', "Bearer $appToken->token");

        $response = (new AllowedApps())->handle($request, fn () => response()->json(['message' => 'Success!']));

        $this->assertEquals(Response::HTTP_OK, $response->status());
        $this->assertEquals(
            expected: 'Success!',
            actual: data_get($response->getOriginalContent(), 'message')
        );
    }
}
