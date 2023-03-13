<?php

namespace Tests\Integration\Repositories\Website\Image;

use App\Models\Website\Image\WebsiteImage;
use App\Models\Website\Website;
use App\Repositories\Website\Image\WebsiteImageRepository;
use App\Repositories\Website\Image\WebsiteImageRepositoryInterface;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Class WebsiteImageRepositoryTest
 *
 * @group DW
 * @group DW_SLIDESHOW
 *
 * @package Tests\Integration\Repositories\Website\Image
 * @coversDefaultClass \App\Repositories\Website\WebsiteUserRepository
 */
class WebsiteImageRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    protected $website;
    protected $images;

    const DEFAULT_DEALER_ID = 1001;
    const NUMBER_OF_IMAGES = 10;

    public function setUp(): void
    {
        parent::setUp();

        $this->createWebsiteAndImages();
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


    public function testUpdate()
    {
        $image = $this->images->first();

        $this->assertDatabaseHas(WebsiteImage::getTableName(), [
            'identifier' => $image->identifier,
            'title' => $image->title,
            'description' => $image->description
        ]);

        $updatedImage = $this->getConcreteRepository()->update([
            'id' => $image->identifier,
            'title' => 'this is a new image title',
            'description' => 'this is a new image description',
        ]);

        $this->assertSame($updatedImage->title, 'this is a new image title');
        $this->assertSame($updatedImage->description, 'this is a new image description');

        $this->assertDatabaseHas(WebsiteImage::getTableName(), [
            'identifier' => $image->identifier,
            'title' => 'this is a new image title',
            'description' => 'this is a new image description'
        ]);
    }

    public function testGetAll()
    {
        $images = $this->getConcreteRepository()->getAll([
            'website_id' => $this->website->id
        ]);

        $this->assertCount(self::NUMBER_OF_IMAGES, $images);
    }

    public function testGetAllExpired()
    {
        $expiredImages = $this->images->take(4);
        $expiredImages->each(function ($image) {
            $image->update(['expires_at' => now()->subHour()]);
        });

        $images = $this->getConcreteRepository()->getAll([
            'expired' => 1,
            'website_id' => $this->website->id
        ]);

        $this->assertCount(4, $images);

        $this->assertSame(4, collect($images->items())->filter(function ($each) use ($expiredImages) {
            return $expiredImages->contains('identifier', $each->identifier);
        })->count());
    }

    public function testGetAllNonExpired()
    {
        $expiredImages = $this->images->take(4);
        $expiredImages->each(function ($image) {
            $image->update(['expires_at' => now()->subHour()]);
        });

        $images = $this->getConcreteRepository()->getAll([
            'expired' => 0,
            'website_id' => $this->website->id
        ]);

        $this->assertCount(6, $images);

        $this->assertSame(6, collect($images->items())->filter(function ($each) {
            return $this->images->where('expires_at', null)->contains('identifier', $each->identifier);
        })->count());
    }

    public function testGetAllImagesExpiredAtAGivenDate()
    {
        $dateExpired = now()->subDays(2);

        $expiredImages = $this->images->take(6);
        $expiredImages->each(function ($image) use ($dateExpired) {
            $image->update(['expires_at' => $dateExpired]);
        });

        $images = $this->getConcreteRepository()->getAll([
            'expires_at' => $dateExpired->toDateString(),
            'website_id' => $this->website->id
        ]);

        $this->assertCount(6, $images);

        $this->assertSame(6, collect($images->items())->filter(function ($each) use ($expiredImages) {
            return $expiredImages->contains('identifier', $each->identifier);
        })->count());
    }

    protected function getConcreteRepository(): WebsiteImageRepository
    {
        return $this->app->make(WebsiteImageRepositoryInterface::class);
    }
}
