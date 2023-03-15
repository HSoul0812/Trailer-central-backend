<?php

namespace Tests\Integration\Repositories\Website\Image;

use App\Models\Website\Image\WebsiteImage;
use App\Models\Website\Website;
use App\Repositories\Website\Image\WebsiteImageRepository;
use App\Repositories\Website\Image\WebsiteImageRepositoryInterface;
use InvalidArgumentException;
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
    /** @var WebsiteImageRepository */
    protected $repository;

    const DEFAULT_DEALER_ID = 1001;
    const NUMBER_OF_IMAGES = 10;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->app->make(WebsiteImageRepositoryInterface::class);
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

        $updatedImage = $this->repository->update([
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
        $images = $this->repository->getAll([
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

        $images = $this->repository->getAll([
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

        $images = $this->repository->getAll([
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

        $images = $this->repository->getAll([
            'expires_at' => $dateExpired->toDateString(),
            'website_id' => $this->website->id
        ]);

        $this->assertCount(6, $images);

        $this->assertSame(6, collect($images->items())->filter(function ($each) use ($expiredImages) {
            return $expiredImages->contains('identifier', $each->identifier);
        })->count());
    }

    public function testCreate()
    {
        $data = [
            'image' => 'http://dashboard.trailercentral.com/website/media/dev/33E98FA3-1273-4878-BB73-6C203F2A61EB.png',
            'title' => 'Test Image Create',
            'is_active' => 1
        ];
        $image = $this->repository->create($data);
        $this->assertDatabaseHas(WebsiteImage::getTableName(), $data);
        $image->delete();
    }

    public function testItValidatesTheDeleteParams()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->repository->delete([]);

        $this->expectException(InvalidArgumentException::class);
        $this->repository->delete(['id']);

        $this->expectException(InvalidArgumentException::class);
        $this->repository->delete(['website_id']);
    }

    public function testDelete()
    {
        $image = $this->images->first();
        $params = ['id' => $image->identifier, 'website_id' => $image->website_id];
        $this->repository->delete($params);
        $this->assertDatabaseMissing(WebsiteImage::getTableName(), [
            'identifier' => $params['id'],
            'website_id' => $params['website_id']
        ]);
    }
}
