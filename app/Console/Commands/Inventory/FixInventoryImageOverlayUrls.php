<?php

namespace App\Console\Commands\Inventory;

use App\Constants\Date as Constants;
use App\Models\Inventory\Inventory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use stdClass as Dealer;
use Illuminate\Support\Facades\Date;

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
        $dealers = DB::table('dealer')->select(['dealer_id', 'overlay_updated_at'])->get();

        $dealers->each(static function (Dealer $dealer): void {
            $dealerOverlayUpdatedAt = empty($dealer->overlay_updated_at) ?
                null :
                Date::createFromFormat(
                    Constants::FORMAT_Y_M_D_T,
                    $dealer->overlay_updated_at
                );

            if ($dealerOverlayUpdatedAt) {
                // one minute after to ensure next time it will not generate overlay
                // (unless some dealer inventory config change), instead it will swap images as expected
                // filename_with_overlay -> filename
                $oneMinuteAfterDealerOverlayUpdatedAt = $dealerOverlayUpdatedAt
                    ->addMinute()
                    ->format(Constants::FORMAT_Y_M_D_T);
            }

            // to be able to swap the image with/without overlay to filename we need to have them
            // stored as concrete columns
            $updateFilenameCompanionsSQL = <<<SQL
                UPDATE image
                    JOIN inventory_image on inventory_image.image_id = image.image_id
                    JOIN inventory on inventory.inventory_id = inventory_image.inventory_id
                SET
                    filename_without_overlay = filename_noverlay,
                    filename_with_overlay = filename,
                    overlay_updated_at = :overlay_updated_at
                WHERE
                    inventory.dealer_id = :dealer_id AND
                    inventory.overlay_enabled IN (:primary_image,:all_images) AND
                    (filename_noverlay IS NOT NULL AND filename_noverlay != '')
SQL;

            DB::statement(
                $updateFilenameCompanionsSQL,
                [
                    'dealer_id' => $dealer->dealer_id,
                    'primary_image' => Inventory::OVERLAY_ENABLED_PRIMARY,
                    'all_images' => Inventory::OVERLAY_ENABLED_ALL,
                    'overlay_updated_at' => $dealerOverlayUpdatedAt ? $oneMinuteAfterDealerOverlayUpdatedAt : null
                ]
            );
        });
    }
}
