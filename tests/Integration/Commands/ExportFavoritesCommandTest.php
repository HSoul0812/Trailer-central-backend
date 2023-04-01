<?php

namespace Tests\Integration\Commands;

use App\Console\Commands\Export\ExportFavoritesCommand;
use App\Mail\Export\FavoritesExportMail;
use App\Models\Inventory\Category;
use App\Models\Inventory\Inventory;
use App\Models\Website\Config\WebsiteConfig;
use App\Models\Website\User\WebsiteUser;
use App\Models\Website\User\WebsiteUserFavoriteInventory;
use App\Models\Website\Website;
use App\Repositories\Export\FavoritesRepositoryInterface;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException as BindingResolutionExceptionAlias;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * @group DW
 * @group DW_INVENTORY
 */
class ExportFavoritesCommandTest extends TestCase
{
    /**
     * @var Website
     */
    private $website;

    /**
     * @var WebsiteUser
     */
    private $user;

    /**
     * @var Category
     */
    private $category;

    /**
     * @var Collection<Inventory>
     */
    private $inventories;

    /**
     * @var WebsiteConfig
     */
    private $scheduleConfig;

    /**
     * @var WebsiteConfig
     */
    private $emailsConfig;

    /**
     * @return void
     * @throws BindingResolutionExceptionAlias
     */
    public function testItSendsReportsToDealersViaEmailWhenCalled()
    {
        Mail::fake();

        $this->createTestData();

        $command = new ExportFavoritesCommand();

        $command->handle(app(WebsiteConfigRepositoryInterface::class), app(FavoritesRepositoryInterface::class));

        Mail::assertSent(FavoritesExportMail::class);

        $this->destroyTestData();
    }

    private function createTestData()
    {
        $this->website = factory(Website::class)->create();
        $this->scheduleConfig = WebsiteConfig::create([
            'website_id' => $this->website->id,
            'key' => 'general/favorites_export_schedule',
            'value' => '0'
        ]);
        $this->emailsConfig = WebsiteConfig::create([
            'website_id' => $this->website->id,
            'key' => 'general/favorites_export_emails',
            'value' => 'john@example.com'
        ]);
        $this->user = factory(WebsiteUser::class)->create(['website_id' => $this->website->id]);
        $this->category = factory(Category::class)->create();
        $this->inventories = factory(Inventory::class, 5)->create(['dealer_id' => $this->website->dealer_id, 'category' => $this->category->inventory_category_id]);
        WebsiteUserFavoriteInventory::insert($this->inventories->map(function ($inventory) {
            return ['website_user_id' => $this->user->id, 'inventory_id' => $inventory->inventory_id];
        })->toArray());
    }

    private function destroyTestData()
    {
        $this->scheduleConfig->delete();
        $this->emailsConfig->delete();
        $this->website->delete();
        $this->user->delete();
        $this->inventories->each(function ($inventory) {
            $inventory->delete();
        });
        $this->category->delete();
    }
}
