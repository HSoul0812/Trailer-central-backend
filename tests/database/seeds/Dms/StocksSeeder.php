<?php

declare(strict_types=1);

namespace Tests\database\seeds\Dms;

use App\Models\Inventory\Inventory;
use App\Models\Parts\Bin;
use App\Models\Parts\BinQuantity;
use App\Models\Parts\CacheStoreTime;
use App\Models\Parts\Part;
use App\Models\Parts\Vendor;
use App\Models\User\DealerLocation;
use App\Models\User\User;
use App\Repositories\Dms\StockRepository;
use App\Traits\WithGetter;
use Exception;
use Tests\database\seeds\Seeder;

/**
 * @property-read array<User> $dealers
 * @property-read array<int, array<Part>> $parts
 * @property-read array<int, array<Bin>> $bins
 * @property-read array<int, array<Inventory>> $units
 */
class StocksSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var array<User>
     */
    private $dealers = [];

    /**
     * @var array<int, array<Part>>
     */
    private $parts = [];

    /**
     * @var array<int, array<Bin>>
     */
    private $bins = [];

    /**
     * @var array<BinQuantity>
     */
    private $partBins = [];

    /**
     * @var array<int, array<Inventory>>
     */
    private $units = [];

    /**
     * @throws Exception when `random_int` was not able to gather sufficient entropy.
     */
    public function seed(): void
    {
        $this->seedDealers();

        $dealer1Id = $this->dealers[0]->getKey();
        $dealer2Id = $this->dealers[1]->getKey();

        $this->bins[$dealer1Id] = factory(Bin::class, 2)->create(['dealer_id' => $dealer1Id])->getDictionary(); // 2 new bins
        $this->bins[$dealer2Id] = factory(Bin::class, 2)->create(['dealer_id' => $dealer2Id])->getDictionary(); // 2 new bins

        $this->parts[$dealer1Id] = factory(Part::class, 2)
            ->create(['dealer_id' => $dealer1Id, 'title' => 'Super duper part'])
            ->each(function (Part $part) use ($dealer1Id) {
                $this->attachPartToRandomBin($part->id, $dealer1Id);
            })->all(); // 2 new parts with fixed title to be able to search by

        $this->parts[$dealer1Id] = array_merge(
            $this->parts[$dealer1Id],
            factory(Part::class, 6)->create(['dealer_id' => $dealer1Id])->each(function (Part $part) use ($dealer1Id) {
                $this->attachPartToRandomBin($part->id, $dealer1Id);
            })->all()
        ); // 6 new parts
        $this->parts[$dealer2Id] = factory(Part::class, 8)->create(['dealer_id' => $dealer2Id])->each(function (Part $part) use ($dealer2Id) {
            $this->attachPartToRandomBin($part->id, $dealer2Id);
        })->all(); // 8 new parts

        $this->units[$dealer1Id] = factory(Inventory::class, 4)->create(['dealer_id' => $dealer1Id])->all(); // 4 new major units
        $this->units[$dealer2Id] = factory(Inventory::class, 4)->create(['dealer_id' => $dealer2Id])->all(); // 4 new major units
    }

    public function seedDealers(): void
    {
        $this->dealers[] = factory(User::class)->create();
        $this->dealers[] = factory(User::class)->create();
    }

    public function buildReport(int $dealerIndex, string $type): array
    {
        $dealerId = $this->dealers[$dealerIndex]->getKey();

        /** @var array<Part> $parts */
        $parts = $this->parts[$dealerId];
        /** @var array<Inventory> $units */
        $units = $this->units[$dealerId];

        $partsReport = [];
        $unitsReport = [];

        if ($this->isTypeMixedOrPartsReport($type)) {
            foreach ($parts as $part) {
                $qtyBin = $this->partBins[$part->id];

                $partsReport[$part->id . '-' . StockRepository::STOCK_TYPE_PARTS][$qtyBin->id]['part'] = (object)[
                    'qty' => $this->partBins[$part->id]->qty,
                    'bin_name' => $this->bins[$part->dealer_id][$qtyBin->bin_id]->bin_name,
                    'bin_id' => $qtyBin->bin_id,
                    'id' => $part->id,
                    'title' => $part->title,
                    'reference' => $part->sku,
                    'price' => (string)$part->price,
                    'dealer_cost' => (string)$part->dealer_cost,
                    'profit' => (string)$part->price - $part->dealer_cost,
                    'source' => StockRepository::STOCK_TYPE_PARTS
                ];
            }
        }

        if ($this->isTypeMixedOrUnitsReport($type)) {
            foreach ($units as $unit) {
                $unitsReport[$unit->inventory_id . '-' . StockRepository::STOCK_TYPE_INVENTORIES][0]['part'] = (object)[
                    'qty' => 1,
                    'bin_name' => 'na',
                    'bin_id' => 0,
                    'id' => $unit->inventory_id,
                    'title' => $unit->title,
                    'reference' => $unit->stock,
                    'price' => (string)$unit->price,
                    'dealer_cost' => (string)$unit->cost_of_unit,
                    'profit' => (string)($unit->price - $unit->cost_of_unit),
                    'source' => StockRepository::STOCK_TYPE_INVENTORIES
                ];
            }
        }

        return array_merge($partsReport, $unitsReport);
    }

    public function cleanUp(): void
    {
        if (!empty($this->dealers)) {
            $dealersId = [$this->dealers[0]->getKey(), $this->dealers[1]->getKey()];

            $parts = array_merge($this->parts[$dealersId[0]], $this->parts[$dealersId[1]]);

            $partsId = collect($parts)->map(static function (Part $part): int {
                return $part->getKey();
            });

            // Database clean up
            Inventory::whereIn('dealer_id', $dealersId)->delete();
            BinQuantity::whereIn('part_id', $partsId)->delete();
            Bin::whereIn('dealer_id', $dealersId)->delete();
            Part::whereIn('dealer_id', $dealersId)->delete();
            Vendor::whereIn('dealer_id', $dealersId)->delete();
            DealerLocation::whereIn('dealer_id', $dealersId)->delete();
            CacheStoreTime::whereIn('dealer_id', $dealersId)->delete();
            User::whereIn('dealer_id', $dealersId)->delete();
        }
    }

    /**
     * @param int $partId
     * @param int $dealerId
     * @throws Exception when `random_int` was not able to gather sufficient entropy.
     */
    private function attachPartToRandomBin(int $partId, int $dealerId): void
    {
        $randomBinKey = array_rand($this->bins[$dealerId], 1);

        $this->partBins[$partId] = BinQuantity::create([
            'part_id' => $partId,
            'bin_id' => $this->bins[$dealerId][$randomBinKey]->id,
            'qty' => random_int(1, 10)
        ]);

        $this->partBins[$partId]->save();
    }

    private function isTypeMixedOrPartsReport(string $type): bool
    {
        return empty($type) || $type === StockRepository::STOCK_TYPE_MIXED || $type === StockRepository::STOCK_TYPE_PARTS;
    }

    private function isTypeMixedOrUnitsReport(string $type): bool
    {
        return empty($type) || $type === StockRepository::STOCK_TYPE_MIXED || $type === StockRepository::STOCK_TYPE_INVENTORIES;
    }
}
