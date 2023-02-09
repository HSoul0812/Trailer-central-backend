<?php

namespace App\Console\Commands\Sync;

use App\Models\Image;
use App\Models\Inventory\Attribute as InventoryAttribute;
use App\Models\Inventory\AttributeValue as InventoryAttributeValue;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryFeature;
use App\Models\Inventory\InventoryImage;
use App\Models\User\DealerLocation;
use App\Models\User\User as Dealer;
use App\Models\Website\Config\WebsiteConfig;
use App\Models\Website\Entity as WebsiteEntity;
use App\Models\Website\Image\WebsiteImage;
use App\Models\Website\PaymentCalculator\Settings as PaymentCalculatorSettings;
use App\Models\Website\Website;
use App\Services\Inventory\InventoryServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SyncWebsiteFromRemoteCommand extends AbstractSyncFromRemoteCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'sync:dealer-website-from-remote {dealer_id} {host} {user} {db=trailercentral} {port=3306}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync a dealer website from remote DB.';

    /**
     * Will sync following tables:
     * - `dealer`
     * - `dealer_location`
     * - `website`
     * - `website_config`
     * - `website_entity`
     * - `inventory`
     * - `eav_attribute`
     * - `eav_attribute_value`
     * - `inventory_feature`
     * - `inventory_image`
     * - `image` (related with inventory)
     * - `website_image`
     *
     * @return void
     */
    public function sync(): void
    {
        /** @var Website $website */
        /** @var Dealer $dealer */

        $dealerId = $this->argument('dealer_id');

        $dealer = Dealer::on('remote')->where('dealer_id', $dealerId)->first();
        $website = Website::on('remote')->where('dealer_id', $dealerId)->first();

        if (is_null($dealer) || is_null($website)) {
            $this->output->error('Dealer or Website does not even exists.');

            return;
        }

        DB::transaction(function () use ($dealer, $website) {
            // to dispatch fewer jobs
            Inventory::disableCacheInvalidationAndSearchSyncing();

            $this->unguard();

            Dealer::query()->updateOrCreate(['dealer_id' => $dealer->dealer_id], $dealer->toArray());
            Website::query()->updateOrCreate(['id' => $website->id], $website->toArray());

            $this->output->writeln('website and dealer tables were synced.');

            $locations = 0;
            $dealerLocationsId = [];

            DealerLocation::on('remote')
                ->where('dealer_id', $dealer->dealer_id)
                ->get()
                ->each(function (DealerLocation $location) use (&$locations, &$dealerLocationsId) {
                    DealerLocation::query()->updateOrCreate(
                        ['dealer_location_id' => $location->dealer_location_id],
                        $location->getOriginal()
                    );

                    $locations++;

                    $dealerLocationsId[$location->dealer_location_id] = $location->dealer_location_id;
                });

            $this->output->writeln(sprintf('%d dealer locations were synced.', $locations));

            WebsiteConfig::query()->where('website_id', $website->id)->delete();

            $websiteConfigs = WebsiteConfig::on('remote')
                ->where('website_id', $website->id)
                ->get()
                ->makeHidden(['id'])
                ->toArray();

            WebsiteConfig::query()->insert($websiteConfigs);

            $this->output->writeln(sprintf('%d website configs were synced.', count($websiteConfigs)));

            WebsiteEntity::query()->where('website_id', $website->id)->delete();

            $entities = WebsiteEntity::on('remote')
                ->where('website_id', $website->id)
                ->get()
                ->toArray();

            WebsiteEntity::query()->insert($entities);

            $this->output->writeln(sprintf('%d website entities were synced.', count($entities)));

            $units = 0;

            Inventory::on('remote')
                ->where('dealer_id', $dealer->dealer_id)
                ->chunk(100, function (Collection $inventories) use (&$units, $dealerLocationsId): void {
                    Inventory::query()->whereIn(
                        'inventory_id',
                        $inventories->pluck('inventory_id')->toArray()
                    )->delete(); // to avoid inventory id collisions

                    $inventories->each(function (Inventory $inventory) use ($dealerLocationsId): void {
                        if (!isset($dealerLocationsId[$inventory->dealer_location_id])) {
                            $this->output->writeln(
                                sprintf(
                                    '[error] inventory %d has %d as location, that location does not belong to this dealer.',
                                    $inventory->inventory_id,
                                    $inventory->dealer_location_id
                                )
                            );

                            return;
                        }

                        Inventory::query()->updateOrCreate(
                            ['inventory_id' => $inventory->inventory_id],
                            $inventory->getOriginal()
                        );

                        InventoryAttributeValue::query()->where('inventory_id', $inventory->inventory_id)
                            ->delete();
                        InventoryFeature::query()->where('inventory_id', $inventory->inventory_id)->delete();
                        Image::query()
                            ->join('inventory_image', 'inventory_image.image_id', '=', 'image.image_id')
                            ->join('inventory', 'inventory.inventory_id', '=', 'inventory_image.inventory_id')
                            ->where('inventory.inventory_id', $inventory->inventory_id)
                            ->delete();
                    });

                    $units += count($inventories);
                });

            $this->output->writeln(sprintf('%d dealer inventory were synced.', $units));

            InventoryAttribute::on('remote')
                ->get()
                ->each(function (InventoryAttribute $attribute): void {
                    InventoryAttribute::query()->updateOrCreate(
                        ['attribute_id' => $attribute->attribute_id],
                        $attribute->toArray()
                    );
                });

            $inventoryAttributesCounter = 0;

            InventoryAttributeValue::on('remote')
                ->join('inventory', 'inventory.inventory_id', '=', 'eav_attribute_value.inventory_id')
                ->where('inventory.dealer_id', $dealer->dealer_id)
                ->whereIn('inventory.dealer_location_id', $dealerLocationsId)
                ->select('eav_attribute_value.*')
                ->cursor() // we were forced to use a cursor here, somehow chunk fails
                ->each(function (InventoryAttributeValue $attributeValue) use (&$inventoryAttributesCounter): void {
                    InventoryAttributeValue::query()->insert($attributeValue->toArray());

                    $inventoryAttributesCounter++;
                });

            $this->output->writeln(sprintf('%d inventory attributes were synced.', $inventoryAttributesCounter));

            $inventoryFeaturesCounter = 0;

            InventoryFeature::on('remote')
                ->join('inventory', 'inventory.inventory_id', '=', 'inventory_feature.inventory_id')
                ->where('inventory.dealer_id', $dealer->dealer_id)
                ->whereIn('inventory.dealer_location_id', $dealerLocationsId)
                ->select('inventory_feature.*')
                ->chunk(1000, function (Collection $inventoryFeatures) use (&$inventoryFeaturesCounter): void {
                    $features  = $inventoryFeatures->map(function (InventoryFeature $feature): array {
                        return $feature->makeHidden(['inventory_feature_id'])->toArray();
                    })->toArray();

                    InventoryFeature::query()->insert($features);

                    $inventoryFeaturesCounter += count($inventoryFeatures);
                });

            $this->output->writeln(sprintf('%d inventory features were synced.', $inventoryFeaturesCounter));

            $inventoryImagesCounter = 0;

            Image::on('remote')
                ->join('inventory_image', 'inventory_image.image_id', '=', 'image.image_id')
                ->join('inventory', 'inventory.inventory_id', '=', 'inventory_image.inventory_id')
                ->where('inventory.dealer_id', $dealer->dealer_id)
                ->whereIn('inventory.dealer_location_id', $dealerLocationsId)
                ->select('image.*')
                ->chunk(700, function (Collection $images) use (&$inventoryImagesCounter): void {
                    $images->each(function (Image $image): void {
                        $localImage = Image::query()->create($image->makeHidden(['image_id'])->toArray());

                        $images = InventoryImage::on('remote')
                            ->where('image_id', $image->image_id)
                            ->get()
                            ->map(function (InventoryImage $image) use ($localImage): array {
                                return $image->makeHidden(['image_id'])->toArray() + ['image_id' => $localImage->image_id];
                            })->toArray();

                        InventoryImage::query()->insert($images);
                    });

                    $inventoryImagesCounter += count($images);
                });

            $this->output->writeln(sprintf('%d inventory images were synced.', $inventoryImagesCounter));

            PaymentCalculatorSettings::query()->where('website_id', $website->id)->delete();

            $paymentCalculatorSettings = PaymentCalculatorSettings::on('remote')
                ->where('website_id', $website->id)
                ->get()
                ->makeHidden(['id'])
                ->toArray();

            PaymentCalculatorSettings::query()->insert($paymentCalculatorSettings);

            $this->output->writeln(
                sprintf(
                    '%d payment calculator settings were synced.',
                    count($paymentCalculatorSettings)
                )
            );

            // for website images we dont need to import dealer promos because that table is actually empty
            $websiteImages = WebsiteImage::on('remote')
                ->where('website_id', $website->id)
                ->get()
                ->makeHidden(['identifier'])
                ->toArray();

            WebsiteImage::query()->insert($websiteImages);

            $this->output->writeln(
                sprintf(
                    '%d website images were synced.',
                    count($websiteImages)
                )
            );

            $this->reguard();
        });

        $this->output->writeln('Dispatching ES indexation jobs...');
        $this->output->writeln('Dispatching Cache invalidation jobs...');

        app(InventoryServiceInterface::class)->invalidateCacheAndReindexByDealerIds([$dealer->dealer_id]);

        $this->output->writeln(sprintf('%s was successfully synced.', $dealer->name));
    }

    private function unguard(): void
    {
        Dealer::unguard();
        Website::unguard();
        WebsiteConfig::unguard();
        DealerLocation::unguard();
        WebsiteEntity::unguard();
        Inventory::unguard();
        PaymentCalculatorSettings::unguard();
        InventoryAttribute::unguard();
        InventoryAttributeValue::unguard();
        InventoryFeature::unguard();
        Image::unguard();
        InventoryImage::unguard();
        WebsiteImage::unguard();
    }

    private function reguard(): void
    {
        Dealer::reguard();
        Website::reguard();
        WebsiteConfig::reguard();
        DealerLocation::reguard();
        WebsiteEntity::reguard();
        Inventory::reguard();
        PaymentCalculatorSettings::reguard();
        InventoryAttribute::reguard();
        InventoryAttributeValue::reguard();
        InventoryFeature::reguard();
        Image::reguard();
        InventoryImage::reguard();
        WebsiteImage::reguard();
    }
}
