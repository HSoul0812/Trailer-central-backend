<?php

namespace Tests\Integration\Commands;

use App\Console\Commands\Website\HideExpiredImages;
use App\Models\Website\Image\WebsiteImage;
use Illuminate\Support\Collection;
use App\Models\Website\Website;
use Tests\TestCase;

class HideExpiredImagesTest extends TestCase
{
    /**
     * @var Website
     */
    private $website;

    /**
     * @var Collection<WebsiteImage>
     */
    private $images;

    /**
     * @var Collection<WebsiteImage>
     */
    private $expiredImages;

    private const EXPIRED_IMAGES_COUNT = 5;

    private const NON_EXPIRED_IMAGES_COUNT = 3;

    public function testItUpdatesActiveToFalseForExpiredImages()
    {
        $this->createTestData();
        $this->assertEquals(self::EXPIRED_IMAGES_COUNT + self::NON_EXPIRED_IMAGES_COUNT, WebsiteImage::where(['title' => 'testItUpdatesActiveToFalseForExpiredImages', 'is_active' => 1])->count());

        $command = new HideExpiredImages();
        $command->handle();

        $this->assertEquals(self::NON_EXPIRED_IMAGES_COUNT, WebsiteImage::where(['title' => 'testItUpdatesActiveToFalseForExpiredImages', 'is_active' => 1])->count());
        $this->assertEquals(self::EXPIRED_IMAGES_COUNT, WebsiteImage::where(['title' => 'testItUpdatesActiveToFalseForExpiredImages', 'is_active' => 0])->count());

        $this->destroyTestData();
    }

    private function createTestData()
    {
        $this->website = factory(Website::class)->create();
        $this->expiredImages = factory(WebsiteImage::class, self::EXPIRED_IMAGES_COUNT)->create([
            'title' => 'testItUpdatesActiveToFalseForExpiredImages',
            'is_active' => 1,
            'expires_at' => now()->subDay()
        ]);

        $this->images = factory(WebsiteImage::class, self::NON_EXPIRED_IMAGES_COUNT)->create([
            'title' => 'testItUpdatesActiveToFalseForExpiredImages',
            'is_active' => 1,
            'expires_at' => now()->addDay()
        ]);
    }

    private function destroyTestData()
    {
        $this->website->delete();
        $imageIds = $this->expiredImages->pluck('identifier')->merge($this->images->pluck('identifier'));
        WebsiteImage::whereIn('identifier', $imageIds->toArray())->delete();
    }
}
