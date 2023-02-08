<?php

namespace Tests\database\seeds\Inventory;

use App\Models\Inventory\Image;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryImage;
use App\Models\User\AuthToken;
use App\Traits\WithGetter;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\database\seeds\Seeder;

/**
 * Class InventoryImageSeeder
 * @package Tests\database\seeds\Inventory
 *
 * @property-read Inventory $inventory
 * @property-read AuthToken $authToken
 * @property-read string $localImageUrl
 * @property-read Image[] $images
 * @property-read InventoryImage[] $inventoryImages
 */
class InventoryImageSeeder extends Seeder
{
    use WithGetter;

    const FILENAME = 'test.png';

    /**
     * @var InventorySeeder
     */
    private $inventorySeeder;

    /**
     * @var Inventory
     */
    private $inventory;

    /**
     * @var AuthToken
     */
    private $authToken;

    /**
     * @var int
     */
    private $numberOfImages;

    /**
     * @var string
     */
    private $localImageUrl;

    /**
     * @var Filesystem
     */
    private $disk;

    /**
     * @var Image[]
     */
    private $images = [];

    /**
     * @var InventoryImage[]
     */
    private $inventoryImages = [];

    public function __construct(array $params = [])
    {
        $this->numberOfImages = $params['numberOfImages'] ?? 0;
    }

    public function seed(): void
    {
        $this->inventorySeeder = new InventorySeeder(['withInventory' => true]);
        $this->inventorySeeder->seed();

        $this->inventory = $this->inventorySeeder->inventory;
        $this->authToken = $this->inventorySeeder->authToken;

        $this->disk = Storage::disk('local_tmp');
        $this->disk->put(self::FILENAME, UploadedFile::fake()->image(self::FILENAME)->get());

        $this->localImageUrl = $this->disk->url(str_replace($this->disk->path(''),'', self::FILENAME));

        if (!$this->numberOfImages) {
            return;
        }

        for ($i = 0; $i < $this->numberOfImages; $i++) {
            /** @var Image $image */
            $image = factory(Image::class)->create();

            $this->inventoryImages[] = factory(InventoryImage::class)->create([
                'image_id' => $image->image_id,
                'inventory_id' => $this->inventory->inventory_id
            ]);

            $this->images[] = $image;
        }
    }

    public function cleanUp(): void
    {
        $this->disk->delete(self::FILENAME);

        $inventoryImages = InventoryImage::query()->where(['inventory_id' => $this->inventory->inventory_id])->get();

        foreach ($inventoryImages as $inventoryImage) {
            $imageId = $inventoryImage->image_id;

            InventoryImage::query()->where(['image_id' => $imageId])->delete();
            Image::query()->where(['image_id' => $imageId])->delete();
        }

        $this->inventorySeeder->cleanUp();
    }
}
