<?php
namespace App\Console\Commands\Website;

use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Services\Inventory\InventoryServiceInterface;
use Illuminate\Console\Command;
use League\HTMLToMarkdown\HtmlConverter;
use League\HTMLToMarkdown\HtmlConverterInterface;

class DescriptionChecker extends Command
{
    /**
     * @var InventoryRepositoryInterface
     */
    protected $inventoryRepository;

    /** @var \Parsedown */
    protected $markdownParser;

    /** @var HtmlConverterInterface */
    protected $htmlToMarkdown;

    /** @var InventoryServiceInterface */
    protected $inventoryService;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "website:description:checker";

    /**
     * @param InventoryRepositoryInterface $inventoryRepository
     * @param \Parsedown $markdownParser
     * @param HtmlConverterInterface $htmlConverter
     * @param InventoryServiceInterface $inventoryService
     */
    public function __construct(
        InventoryRepositoryInterface $inventoryRepository,
        \Parsedown $markdownParser,
        HtmlConverterInterface $htmlConverter,
        InventoryServiceInterface $inventoryService
    )
    {
        parent::__construct();

        $this->inventoryRepository = $inventoryRepository;
        $this->markdownParser = $markdownParser;
        $this->htmlToMarkdown = $htmlConverter;
        $this->inventoryService = $inventoryService;
    }

    public function handle() {

        $inventories = $this->inventoryRepository->getAll([], true, false, ['inventory.inventory_id', 'inventory.description', 'inventory.description_html']);

        foreach ($inventories as $inventory) {

            if (empty($inventory->description) && empty($inventory->description_html)) {
                continue;
            }

            if (!empty($inventory->description)) {
                $inventory->description = strip_tags($inventory->description);
                $inventory->description_html = $this->inventoryService->convertMarkdown($inventory->description);
            }

            if (empty($inventory->description) && !empty($inventory->description_html)) {
                $inventory->description = $this->htmlToMarkdown->convert($inventory->description_html);
            }

            $inventory->save();
        }
    }
}
