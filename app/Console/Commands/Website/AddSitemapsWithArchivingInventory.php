<?php

namespace App\Console\Website\Commands;

use App\Models\Website\Config\WebsiteConfigDefault;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\Repository;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Repositories\Website\EntityRepositoryInterface;
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
     * @var App\Repositories\Website\Config\WebsiteConfigRepositoryInterface
     */
    protected $websiteConfigRepository;

    /**
     * @var InventoryRepositoryInterface
     */
    protected $inventoryRepository;
    
    /**
     * @var App\Repositories\Website\EntityRepositoryInterface
     */
    protected $websiteEntityRepository;

    /**
     * @var string
     */
    protected $filenameTemplate;

    /**
     * AddSitemapsWithArchivingInventory constructor.
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param InventoryRepositoryInterface $inventoryRepository
     */
    public function __construct(WebsiteConfigRepositoryInterface $websiteConfigRepository, InventoryRepositoryInterface $inventoryRepository, EntityRepositoryInterface $websiteEntityRepository)
    {
        parent::__construct();

        $this->websiteConfigRepository = $websiteConfigRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->websiteEntityRepository = $websiteEntityRepository;
        
        $this->filenameTemplate = 'archived_inventory-%s-' . date('Y-m-d')  . '.xml';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $websiteConfigs = $this->websiteConfigRepository->getAll([
            'key' => WebsiteConfigDefault::CONFIG_INCLUDE_ARCHIVING_INVENTORY,
            'value' => 1
        ]);
        
        foreach ($websiteConfigs as $config) {
            $website = $config->website;
            
            // May need to support multiple dealer_id as website do
            $inventories = $this->inventoryRepository->getAll([
                Repository::CONDITION_AND_WHERE =>[
                    ['dealer_id', '=', $website->dealer_id]
                ]
            ], false);

            $tmpFile = storage_path('app/sitemaps') . '/' . sprintf($this->filenameTemplate, $website->dealer_id);
            $domain = 'https://www.' . $website->domain;

            resolve('url')->forceRootUrl($domain);
            $sitemap = Sitemap::create();
            
            $pages = $this->websiteEntityRepository->getAllPages($website->id);

            foreach ($inventories as $inventory) {                
                $sitemap->add(Url::create($inventory->getUrl()));
            }
            
            foreach ($pages as $page) {
                $sitemap->add(Url::create("/{$page->url_path}"));
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
        
        echo $filePath . PHP_EOL;
        
        return Storage::disk('s3')->put($filePath, file_get_contents($tmpFile), 'public');
    }
}
