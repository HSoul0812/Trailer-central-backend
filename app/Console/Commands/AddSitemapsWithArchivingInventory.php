<?php

namespace App\Console\Commands;

use App\Models\Website\Config\WebsiteConfigDefault;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\Repository;
use App\Repositories\Website\WebsiteRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

/**
 * Class AddSitemapsWithArchivingInventory
 * @package App\Console\Commands
 */
class AddSitemapsWithArchivingInventory extends Command
{
    private const FILENAME_TEMPLATE = '%s_sitemap.xml';
    private const FILEPATH_TEMPLATE = 'sitemaps/%s';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "add:sitemaps_with_archiving_inventory";

    /**
     * @var WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * @var InventoryRepositoryInterface
     */
    protected $inventoryRepository;

    /**
     * @var string
     */
    protected $filenameTemplate;

    /**
     * AddSitemapsWithArchivingInventory constructor.
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param InventoryRepositoryInterface $inventoryRepository
     */
    public function __construct(WebsiteRepositoryInterface $websiteRepository, InventoryRepositoryInterface $inventoryRepository)
    {
        parent::__construct();

        $this->websiteRepository = $websiteRepository;
        $this->inventoryRepository = $inventoryRepository;

        $this->filenameTemplate = 'archived_inventory-%s-' . date('Y-m-d')  . '.xml';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $websites = $this->websiteRepository->getAllByConfigParams([
            'config' => [
                WebsiteConfigDefault::CONFIG_INCLUDE_ARCHIVING_INVENTORY => 1
            ]
        ]);

        foreach ($websites as $website) {
            $inventories = $this->inventoryRepository->getAll([
                Repository::CONDITION_AND_WHERE =>[
                    ['dealer_id', '=', $website->dealer_id],
                    ['is_archived', '=', 1]
                ]
            ], false);

            $tmpFile = env('APP_TMP_DIR') . '/' . sprintf($this->filenameTemplate, $website->dealer_id);
            $domain = 'https://' . $website->domain;

            resolve('url')->forceRootUrl($domain);
            $sitemap = Sitemap::create();

            foreach ($inventories as $inventory) {
                $sitemap->add(Url::create($inventory->getUrl()));
            }

            $filename = sprintf(self::FILENAME_TEMPLATE, $website->id);

            $sitemap->writeToFile($tmpFile);

            $this->putToS3($filename, $tmpFile);

            unlink($tmpFile);
        }

        return true;
    }

    /**
     * @param string $domain
     * @param string $filename
     * @param string $tmpFile
     * @return bool
     */
    private function putToS3(string $filename, string $tmpFile)
    {
        $filePath = sprintf(self::FILEPATH_TEMPLATE, $filename);

        return Storage::disk('s3')->put($filePath, file_get_contents($tmpFile));
    }
}
