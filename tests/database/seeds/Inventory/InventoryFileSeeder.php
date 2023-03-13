<?php

namespace Tests\database\seeds\Inventory;

use App\Models\Inventory\File;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryFile;
use App\Models\User\AuthToken;
use App\Traits\WithGetter;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\database\seeds\Seeder;

/**
 * Class InventoryFileSeeder
 * @package Tests\database\seeds\Inventory
 *
 * @property-read Inventory $inventory
 * @property-read AuthToken $authToken
 * @property-read string $localFileUrl
 * @property-read File[] $files
 * @property-read InventoryFile[] $inventoryFiles
 */
class InventoryFileSeeder extends Seeder
{
    use WithGetter;

    const FILENAME = 'test.txt';

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
    private $numberOfFiles;

    /**
     * @var string
     */
    private $localFileUrl;

    /**
     * @var Filesystem
     */
    private $disk;

    /**
     * @var File[]
     */
    private $files = [];

    /**
     * @var InventoryFile[]
     */
    private $inventoryFiles = [];

    public function __construct(array $params = [])
    {
        $this->numberOfFiles = $params['numberOfFiles'] ?? 0;
    }

    public function seed(): void
    {
        $this->inventorySeeder = new InventorySeeder(['withInventory' => true]);
        $this->inventorySeeder->seed();

        $this->inventory = $this->inventorySeeder->inventory;
        $this->authToken = $this->inventorySeeder->authToken;

        $this->disk = Storage::disk('local_tmp');
        $this->disk->put(self::FILENAME, UploadedFile::fake()->create(self::FILENAME, '1000')->get());

        $this->localFileUrl = $this->disk->url(str_replace($this->disk->path(''),'', self::FILENAME));

        if (!$this->numberOfFiles) {
            return;
        }

        for ($i = 0; $i < $this->numberOfFiles; $i++) {
            /** @var File $file */
            $file = factory(File::class)->create();

            $this->inventoryFiles[] = factory(InventoryFile::class)->create([
                'file_id' => $file->id,
                'inventory_id' => $this->inventory->inventory_id
            ]);

            $this->files[] = $file;
        }
    }

    public function cleanUp(): void
    {
        $this->disk->delete(self::FILENAME);

        $inventoryFiles = InventoryFile::query()->where(['inventory_id' => $this->inventory->inventory_id])->get();

        foreach ($inventoryFiles as $inventoryFile) {
            $fileId = $inventoryFile->file_id;

            InventoryFile::query()->where(['file_id' => $fileId])->delete();
            File::query()->where(['id' => $fileId])->delete();
        }

        $this->inventorySeeder->cleanUp();
    }
}
