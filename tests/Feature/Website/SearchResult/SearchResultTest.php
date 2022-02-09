<?php

namespace Tests\Feature\Website\SearchResult;

use App\Models\Website\User\WebsiteUser;
use App\Models\Website\User\WebsiteUserToken;
use App\Models\Website\Website;
use Dingo\Api\Http\Response;
use Tests\TestCase;

class SearchResultTest extends TestCase
{
    protected $website;

    /** @var WebsiteUser */
    protected $websiteUser;

    /** @var WebsiteUserToken */
    protected $websiteUserToken;

    public function setUp(): void
    {
        parent::setUp();

        $this->website = factory(Website::class)->create();

        $this->websiteUser = factory(WebsiteUser::class)->create([
            'first_name' => 'Test',
            'middle_name' => '',
            'last_name' => 'User',
            'email' => 'websiteusertest@test.com',
            'website_id' => $this->website->id,
            'password' => '1234',
        ]);

        $this->websiteUserToken = factory(WebsiteUserToken::class)->create([
            'website_user_id' => $this->websiteUser->id,
            'access_token' => 'TestUserToken'
        ]);
    }

    public function testNonAuthUserCreate()
    {
        $response = $this
            ->withHeaders(['user-access-token' => 'non_exist_token_or_invalid_token'])
            ->post('api/website/user/search-result', [
               'search_url' => 'test_search_url'
            ]);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testAuthUserCanCreate()
    {
        $newUrl = 'test_search_url';

        $response = $this
            ->withHeaders(['user-access-token' => $this->websiteUserToken->access_token])
            ->post('api/website/user/search-result', [
                'search_url' => $newUrl
            ]);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        $this->assertEquals($newUrl, $content['data']['search_url']);
        $this->assertEquals($this->websiteUser->id, $content['data']['website_user_id']);
    }

    public function testAuthUserCantCreateSameUrl()
    {
        $newUrl = 'test_search_url';

        // First Request for Same Url should be OK
        $response = $this
            ->withHeaders(['user-access-token' => $this->websiteUserToken->access_token])
            ->post('api/website/user/search-result', [
                'search_url' => $newUrl
            ]);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Second Request for Same Url should not be OK
        $response = $this
            ->withHeaders(['user-access-token' => $this->websiteUserToken->access_token])
            ->post('api/website/user/search-result', [
                'search_url' => $newUrl
            ]);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        $this->assertEquals('Search result already saved before.', $content['message']);
    }

    public function testNonAuthUserCantGet()
    {
        $response = $this
            ->withHeaders(['user-access-token' => 'non_exist_token_or_invalid_token'])
            ->get('api/website/user/search-result');

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testAuthUserCanGet()
    {
        $response = $this
            ->withHeaders(['user-access-token' => $this->websiteUserToken->access_token])
            ->post('api/website/user/search-result', [
                'search_url' => 'sample_url'
            ]);

        $response = $this
            ->withHeaders(['user-access-token' => $this->websiteUserToken->access_token])
            ->get('api/website/user/search-result');

        $content = json_decode($response->getContent(), true);

        $this->assertIsArray($content['data']);
        $this->assertEquals('sample_url', $content['data'][0]['search_url']);
        $this->assertEquals($this->websiteUser->id, $content['data'][0]['website_user_id']);
    }

    public function tearDown(): void
    {
        if ($this->website) {
            $this->website->delete();
        }

        if ($this->websiteUser) {
            $this->websiteUser->delete();
        }

        parent::tearDown();
    }
}
