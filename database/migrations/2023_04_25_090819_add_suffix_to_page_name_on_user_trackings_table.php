<?php

use App\Models\UserTracking;
use Illuminate\Database\Migrations\Migration;

class AddSuffixToPageNameOnUserTrackingsTable extends Migration
{
    public const NAME_MAPPINGS = [
        'TT_PLP' => 'TT_PLP_PAGE',
        'TT_PDP' => 'TT_PDP_PAGE',
        'TT_DEALER' => 'TT_DEALER_PAGE',
    ];

    /**
     * Run the migrations.
     */
    public function up()
    {
        foreach (self::NAME_MAPPINGS as $from => $to) {
            UserTracking::where('page_name', $from)->update([
                'page_name' => $to,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }
}
