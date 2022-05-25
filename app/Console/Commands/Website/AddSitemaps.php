<?php

namespace App\Console\Commands\Website;

use App\Models\Website\Config\WebsiteConfigDefault;
use App\Models\Website\Website;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\Repository;
use App\Repositories\Website\EntityRepositoryInterface;
use App\Repositories\Website\WebsiteRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

/**
 * Class AddSitemaps
 * @package App\Console\Commands
 */
class AddSitemaps extends Command
{
    private const FILENAME_TEMPLATE = '%s_sitemap.xml';
    private const FILEPATH_TEMPLATE = 'sitemaps/%s';
    private const LOCAL_FILENAME_TEMPLATE = 'sitemaps-%s-%s.xml';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "add:sitemaps";

    /**
     * @var InventoryRepositoryInterface
     */
    protected $inventoryRepository;

    /**
     * @var EntityRepositoryInterface
     */
    protected $websiteEntityRepository;

    /**
     * @var WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * AddSitemaps constructor.
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param InventoryRepositoryInterface $inventoryRepository
     * @param EntityRepositoryInterface $websiteEntityRepository
     */
    public function __construct(WebsiteRepositoryInterface $websiteRepository, InventoryRepositoryInterface $inventoryRepository, EntityRepositoryInterface $websiteEntityRepository)
    {
        parent::__construct();

        $this->inventoryRepository = $inventoryRepository;
        $this->websiteEntityRepository = $websiteEntityRepository;
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $websites = $this->websiteRepository->getAll([
            Repository::CONDITION_AND_WHERE => [
                ['type', '!=', Website::WEBSITE_TYPE_CLASSIFIED]
            ]
        ]);

        foreach ($websites as $website) {
            $tmpFile = storage_path('app/sitemaps') . '/' . sprintf(self::LOCAL_FILENAME_TEMPLATE, $website->dealer_id, date('Y-m-d_H-i-s'));
            $domain = 'https://www.' . $website->domain;

            resolve('url')->forceRootUrl($domain);
            $sitemap = Sitemap::create();

            $includeArchiving = $website->websiteConfigByKey(WebsiteConfigDefault::CONFIG_INCLUDE_ARCHIVING_INVENTORY);

            $withDefault = !$includeArchiving;

            $sitemap->add(Url::create("/"));

            $pages = $this->websiteEntityRepository->getAllPages($website->id);

            foreach ($pages as $page) {
                $sitemap->add(Url::create("/{$page->url_path}"));
            }

            $inventories = $this->inventoryRepository->getAll([
                Repository::CONDITION_AND_WHERE =>[
                    ['dealer_id', '=', $website->dealer_id]
                ]
            ], $withDefault);

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
     * @param string $filename
     * @param string $tmpFile
     * @return bool
     */
    private function putToS3(string $filename, string $tmpFile)
    {
        $filePath = sprintf(self::FILEPATH_TEMPLATE, $filename);

        echo $filePath . PHP_EOL;

        return Storage::disk('s3')->put($filePath, file_get_contents($tmpFile));
    }
}
