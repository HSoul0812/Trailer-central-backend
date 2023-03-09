<?php

namespace App\Console\Commands\Inventory;

use App\Jobs\Inventory\GenerateOverlayImageJob;
use App\Services\Inventory\InventoryServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB as Query;
use stdClass as Row;

class FixInventoryImagesOverlays extends Command
{
    private const OVERLAY_ENABLED = 1;

    private const DO_NOT_REINDEX_AND_INVALIDATE = false;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'inventory:fix-image-overlays';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Will regenerate all image overlays of every single inventory which has enabled it';

    /** @var InventoryServiceInterface */
    private $service;

    public function __construct(InventoryServiceInterface $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    public function handle()
    {
        $dealers = Query::table('inventory')
            ->distinct()
            ->select('inventory.dealer_id')
            ->leftJoin('dealer', 'dealer.dealer_id', '=', 'inventory.dealer_id')
            ->where('inventory.overlay_enabled', '>=', self::OVERLAY_ENABLED)
            ->where(function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query
                        ->where('dealer.overlay_logo_position', 'NOT LIKE', 'upper_%')
                        ->where('dealer.overlay_upper', '=', 'phone');
                })->orWhere(function (Builder $query): void {
                    $query
                        ->where('dealer.overlay_logo_position', 'NOT LIKE', 'lower_%')
                        ->where('dealer.overlay_lower', '=', 'phone');
                });
            })
            ->get();

        $dealers->each(function (Row $dealer): void {
            $cursor = Query::table('inventory')
                ->select('inventory.inventory_id')
                ->leftJoin('dealer', 'dealer.dealer_id', '=', 'inventory.dealer_id')
                ->where('inventory.dealer_id', '=', $dealer->dealer_id)
                ->where('inventory.overlay_enabled', '>=', self::OVERLAY_ENABLED)
                ->where(function (Builder $query): void {
                    $query->where(function (Builder $query): void {
                        $query
                            ->where('dealer.overlay_logo_position', 'NOT LIKE', 'upper_%')
                            ->where('dealer.overlay_upper', '=', 'phone');
                    })->orWhere(function (Builder $query): void {
                        $query
                            ->where('dealer.overlay_logo_position', 'NOT LIKE', 'lower_%')
                            ->where('dealer.overlay_lower', '=', 'phone');
                    });
                })
                ->cursor();

            $cursor->each(function (Row $inventory): void {
                dispatch(new GenerateOverlayImageJob(
                    $inventory->inventory_id,
                    self::DO_NOT_REINDEX_AND_INVALIDATE
                ))->onQueue('overlay-images');
            });

            $this->service->invalidateCacheAndReindexByDealerIds([$dealer->dealer_id]);
        });
    }
}
