<?php

use App\Models\CRM\Leads\LeadSource;
use Illuminate\Database\Migrations\Migration;

class RemoveSomeDefaultLeadSources extends Migration
{
    /** @const array */
    public const SOURCE_DRIVE_BY = ["lead_source_id" => 4, "user_id" => 0, "source_name" => "Drive By"];

    /** @const array */
    public const SOURCE_PAST_CUSTOMER = ["lead_source_id" => 11, "user_id" => 0, "source_name" => "Past Customer"];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        LeadSource::where(self::SOURCE_DRIVE_BY)->delete();
        LeadSource::where(self::SOURCE_PAST_CUSTOMER)->delete();
        $this->removeSourcesAsParents(self::SOURCE_DRIVE_BY['lead_source_id']);
        $this->removeSourcesAsParents(self::SOURCE_PAST_CUSTOMER['lead_source_id']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        LeadSource::create(self::SOURCE_DRIVE_BY);
        LeadSource::create(self::SOURCE_PAST_CUSTOMER);
    }

    private function removeSourcesAsParents($source_id)
    {
        $sources = LeadSource::where('parent_id', '=', $source_id)->get();

        foreach ($sources as $source) {
            $source->parent_id = null;
            $source->save();
        }
    }
}
