<?php

namespace Tests\Feature\Website\Image;

use App\Models\Website\Image\WebsiteImage;
use Tests\TestCase;
use App\Models\Website\Website;

class WebsiteImageTest extends TestCase
{
    protected $website;
    protected $images;

    const DEFAULT_DEALER_ID = 1001;
    const NUMBER_OF_IMAGES = 10;

    public function setUp(): void
    {
        parent::setUp();

        $this->createWebsiteAndImages();
    }

    protected function tearDown(): void
    {
        $this->tearDownSeed();

        parent::tearDown();
    }

    protected function tearDownSeed(): void
    {
        $this->website->delete();

        $this->images->each(function ($image) {
            $image->delete();
        });
    }

    protected function createWebsiteAndImages()
    {
        $this->website = factory(Website::class)->create([
            'dealer_id' => self::DEFAULT_DEALER_ID
        ]);
        $this->images = factory(WebsiteImage::class, self::NUMBER_OF_IMAGES)->create([
            'website_id' => $this->website->id
        ]);
    }

    public function testGetAllImagesWithouthAccessToken()
    {
        $response = $this->get('/api/website/' . $this->website->id . '/images');

        $response->assertStatus(403);
    }

    public function testGetAllImages()
    {
        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->get('/api/website/' . $this->website->id . '/images');
        $response->assertStatus(200);

        $this->assertSame(self::NUMBER_OF_IMAGES, WebsiteImage::where('website_id', $this->website->id)->count());

        $data = json_decode($response->getContent(), true);

        $this->assertCount(self::NUMBER_OF_IMAGES, $data['data']);
    }

    public function testGetExpiredImages()
    {
        $expiredImages = $this->images->take(4);
        $expiredImages->each(function ($image) {
            $image->update(['expires_at' => now()->subHour()]);
        });

        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->get('/api/website/' . $this->website->id . '/images?expired=1');
        $response->assertStatus(200);

        $data = json_decode($response->getContent(), true);

        $this->assertCount(4, $data['data']);
    }

    public function testGetNonExpiredImages()
    {
        $expiredImages = $this->images->take(4);
        $expiredImages->each(function ($image) {
            $image->update(['expires_at' => now()->subHour()]);
        });

        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->get('/api/website/' . $this->website->id . '/images?expired=0');
        $response->assertStatus(200);

        $data = json_decode($response->getContent(), true);

        $this->assertCount(6, $data['data']);
    }

    public function testUpdateImageWithoutAccessToken()
    {
        $image = $this->images->first();

        $response = $this->post('/api/website/' . $this->website->id . '/image/' . $image->identifier, []);

        $response->assertStatus(403);
    }

    public function testUpdateImage()
    {
        $image = $this->images->first();

        $data = [
            'title' => 'This is a new image title',
            'description' => 'This is a new image description',
            'image' => 'https://example.com/images/image.png'
        ];

        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->post('/api/website/' . $this->website->id . '/image/' . $image->identifier, $data);

        $response->assertStatus(200);

        $this->assertDatabaseHas(WebsiteImage::getTableName(), [
            'identifier' => $image->identifier,
            'website_id' => $this->website->id
        ] + $data);
    }

    public function testUpdateImageRequestValidation()
    {
        $data = [
            'id' => 0,
            'expires_at' => 'today'
        ];

        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->post('/api/website/' . $this->website->id . '/image/' . 0, $data);

        $response->assertStatus(422);
        $json = json_decode($response->getContent(), true);

        self::assertArrayHasKey('message', $json);
        self::assertArrayHasKey('id', $json['errors']);
        self::assertArrayHasKey('expires_at', $json['errors']);
    }
}
