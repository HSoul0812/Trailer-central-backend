<?php

namespace App\Observers\Website;

use App\Models\Inventory\CustomOverlay;
use App\Models\Website\Website;
use Illuminate\Support\Facades\DB;

/**
 * Class WebsiteObserver
 */
class WebsiteObserver
{
    /**
     * @param Website $website
     * @throws \Exception
     */
    public function saving(Website $website): void
    {
        $custom_overlays = DB::table('custom_overlays')->where(['dealer_id' => $website->dealer_id])->get();

        if ($custom_overlays->isEmpty()) {
            foreach (CustomOverlay::VALID_CUSTOM_NAMES as $custom_overlay_valid) {
                $new_custom = new CustomOverlay();
                $new_custom->name = $custom_overlay_valid;
                $new_custom->dealer_id = $website->dealer_id;
                $new_custom->save();
            }
        }
    }

}
