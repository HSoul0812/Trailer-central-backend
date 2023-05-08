<?php

namespace App\Console\Commands\Inventory\Images;

use App\Jobs\Inventory\GenerateOverlayImageJob;
use App\Jobs\Job;
use App\Models\BatchedJob;
use App\Services\Inventory\InventoryServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB as Query;
use stdClass as Row;

class FixWrongPrimaryImageInventoryImagesOverlays extends Command
{
    private const DO_NOT_REINDEX_AND_INVALIDATE = false;

    private const MONITORED_QUEUES = [GenerateOverlayImageJob::LOW_PRIORITY_QUEUE];

    private const MONITORED_GROUP = 'inventory:fix-wrong-primary-images-for-image-overlays';

    private const WAIT_TIME_IN_SECONDS = 15;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'inventory:fix-wrong-primary-images-for-image-overlays {dealer_ids}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Will regenerate all image overlays of every single inventory which has enabled it and has wrong primary image';

    /** @var InventoryServiceInterface */
    private $service;

    public function __construct(InventoryServiceInterface $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    public function handle()
    {
        /** @var int $dealerIds */
        $dealerIds = explode(',', $this->argument('dealer_ids'));

        $dealers = Query::table('inventory')
            ->distinct()
            ->select('inventory.dealer_id')
            ->leftJoin('dealer', 'dealer.dealer_id', '=', 'inventory.dealer_id')
            ->whereIn('dealer.dealer_id', $dealerIds)
            ->get();

        $dealers->each(function (Row $dealer): void {
            $numberOfJobs = 0;

            Job::batch(
                function (BatchedJob $batch) use ($dealer, &$numberOfJobs) {
                    $sql = <<<SQL
                    SELECT inventory_id FROM (
                                SELECT i.inventory_id FROM inventory i
                                    JOIN inventory_image ii ON i.inventory_id = ii.inventory_id
                                WHERE i.dealer_id = :dealer_id AND i.overlay_enabled = 1
                                GROUP BY i.inventory_id
                                -- only inventories which have images, and those images dont have a primary image already setup
                                HAVING SUM(ii.is_default) = 0
                        ) as ListOfInventories;
SQL;

                    $cursor = Query::cursor(Query::raw($sql), ['dealer_id' => $dealer->dealer_id]);

                    foreach ($cursor as $inventory) {
                        dispatch(new GenerateOverlayImageJob(
                                $inventory->inventory_id,
                                self::DO_NOT_REINDEX_AND_INVALIDATE
                            )
                        )->onQueue(GenerateOverlayImageJob::LOW_PRIORITY_QUEUE);

                        $numberOfJobs++;
                    }

                    $this->line(
                        sprintf(
                            'It was dispatched [<comment>%d</comment>] jobs for dealer [<comment>%d</comment>]',
                            $numberOfJobs,
                            $dealer->dealer_id
                        )
                    );
                },
                self::MONITORED_QUEUES,
                self::MONITORED_GROUP.'-'.$dealer->dealer_id,
                self::WAIT_TIME_IN_SECONDS
            );

            $this->service->invalidateCacheAndReindexByDealerIds([$dealer->dealer_id]);
        });
    }
}
