<?php

use Illuminate\Database\Migrations\Migration;
use stdClass as Dealer;

class FixFilenameOriginalImage extends Migration
{
    public function up(): void
    {
        $dealers = DB::table('dealer')->select('dealer_id')->get();

        $dealers->each(static function (Dealer $dealer): void {
            // to be able to swap the image with/without overlay to filename we need to have them stored as concrete columns
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

    public function down(): void
    {
        // nothing to do
    }
}
