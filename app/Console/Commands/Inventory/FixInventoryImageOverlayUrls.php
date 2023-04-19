<?php

namespace App\Console\Commands\Inventory;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use stdClass as Dealer;

class FixInventoryImageOverlayUrls extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'inventory:fix-image-overlay-urls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Will fix the columns values for `filename_without_overlay` and `filename_with_overlay`';

    public function handle()
    {
        $dealers = DB::table('dealer')->select('dealer_id')->get();

        $dealers->each(static function (Dealer $dealer): void {
            // to be able to swap the image with/without overlay to filename we need to have them
            // stored as concrete columns
            $updateFilenameCompanionsSQL = <<<SQL
                UPDATE image
                    JOIN inventory_image on inventory_image.image_id = image.image_id
                    JOIN inventory on inventory.inventory_id = inventory_image.inventory_id
                SET
                    filename_without_overlay = IF(filename_noverlay IS NOT NULL AND filename_noverlay != '', filename_noverlay, filename),
                    filename_with_overlay = IF(filename_noverlay IS NOT NULL AND filename_noverlay != '', filename, NULL)
                WHERE inventory.dealer_id = :dealer_id
SQL;

            DB::statement($updateFilenameCompanionsSQL, ['dealer_id' => $dealer->dealer_id]);
        });
    }
}
