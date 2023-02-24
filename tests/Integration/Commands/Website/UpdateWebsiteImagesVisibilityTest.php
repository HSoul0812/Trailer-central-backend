<?php

namespace Tests\Integration\Commands\Website;

use App\Console\Commands\Website\UpdateWebsiteImagesVisibility;
use App\Models\Website\Image\WebsiteImage;
use Illuminate\Support\Collection;
use App\Models\Website\Website;
use Tests\TestCase;

/**
 * @group DW
 * @group DW_SLIDESHOW
 */
class UpdateWebsiteImagesVisibilityTest extends TestCase
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

    /**
     * @var Collection<WebsiteImage>
     */
    private $futureImages;

    private const STARTS_FROM_FUTURE_IMAGES_COUNT = 9;

    private const EXPIRED_IMAGES_COUNT = 5;

    private const NON_EXPIRED_IMAGES_COUNT = 3;

    public function testItUpdatesTheVisibilityOfTheImagesBasedOnStartsFromAndExpiry()
    {
        $this->createTestData();
        $this->assertEquals(self::EXPIRED_IMAGES_COUNT + self::NON_EXPIRED_IMAGES_COUNT, WebsiteImage::where(['title' => 'testItUpdatesActiveToFalseForExpiredImages', 'is_active' => 1])->count());
        $this->assertEquals(self::STARTS_FROM_FUTURE_IMAGES_COUNT, WebsiteImage::where(['title' => 'testItUpdatesActiveToFalseForExpiredImages', 'is_active' => 0])->count());

        $command = new UpdateWebsiteImagesVisibility();
        $command->handle();

        $this->assertEquals(self::NON_EXPIRED_IMAGES_COUNT + self::STARTS_FROM_FUTURE_IMAGES_COUNT, WebsiteImage::where(['title' => 'testItUpdatesActiveToFalseForExpiredImages', 'is_active' => 1])->count());
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

        $this->futureImages = factory(WebsiteImage::class, self::STARTS_FROM_FUTURE_IMAGES_COUNT)->create([
            'title' => 'testItUpdatesActiveToFalseForExpiredImages',
            'is_active' => 0,
            'starts_from' => now()->subDays(5)
        ]);
    }

    private function destroyTestData()
    {
        $this->website->delete();
        $imageIds = $this->expiredImages->pluck('identifier')
            ->merge($this->images->pluck('identifier'))->merge($this->futureImages->pluck('identifier'));
        WebsiteImage::whereIn('identifier', $imageIds->toArray())->delete();
    }
}
