<?php

use App\Models\CRM\Leads\LeadSource;
use Illuminate\Database\Migrations\Migration;

class AddNewCrmDefaultLeadSources extends Migration
{
    /** @const array */
    public const SOURCE_TWITTER = ["user_id" => 0, "source_name" => "Twitter"];

    /** @const array */
    public const SOURCE_SNAPCHAT = ["user_id" => 0, "source_name" => "Snapchat"];

    /** @const array */
    public const SOURCE_INSTAGRAM = ["user_id" => 0, "source_name" => "Instagram"];

    /** @const array */
    public const SOURCE_GOOGLE_SEARCH = ["user_id" => 0, "source_name" => "Google Search"];

    /** @const array */
    public const SOURCE_TV_ADD = ["user_id" => 0, "source_name" => "TV Add"];

    /** @const array */
    public const SOURCE_LOCAL = ["user_id" => 0, "source_name" => "Local"];

    /** @const array */
    public const SOURCE_DEALER_SITE = ["user_id" => 0, "source_name" => "Dealer Site"];

    /** @const array */
    public const SOURCE_SERVICE_CAMPAIGN = ["user_id" => 0, "source_name" => "Service Campaign"];

    /** @const array */
    public const SOURCE_PARTS_CAMPAIGN = ["user_id" => 0, "source_name" => "Parts Campaign"];

    /** @const array */
    public const SOURCE_OWNERSHIP_CAMPAIGN = ["user_id" => 0, "source_name" => "Ownership Campaign"];

    /** @const array */
    public const SOURCE_FOLLOW_UP_CAMPAIGN = ["user_id" => 0, "source_name" => "Follow Up Campaign"];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Insert Lead Sources
        LeadSource::create(self::SOURCE_TWITTER);
        LeadSource::create(self::SOURCE_SNAPCHAT);
        LeadSource::create(self::SOURCE_INSTAGRAM);
        LeadSource::create(self::SOURCE_GOOGLE_SEARCH);
        LeadSource::create(self::SOURCE_TV_ADD);
        LeadSource::create(self::SOURCE_LOCAL);
        LeadSource::create(self::SOURCE_DEALER_SITE);
        LeadSource::create(self::SOURCE_SERVICE_CAMPAIGN);
        LeadSource::create(self::SOURCE_PARTS_CAMPAIGN);
        LeadSource::create(self::SOURCE_OWNERSHIP_CAMPAIGN);
        LeadSource::create(self::SOURCE_FOLLOW_UP_CAMPAIGN);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        LeadSource::where(self::SOURCE_TWITTER)->delete();
        LeadSource::where(self::SOURCE_SNAPCHAT)->delete();
        LeadSource::where(self::SOURCE_INSTAGRAM)->delete();
        LeadSource::where(self::SOURCE_GOOGLE_SEARCH)->delete();
        LeadSource::where(self::SOURCE_TV_ADD)->delete();
        LeadSource::where(self::SOURCE_LOCAL)->delete();
        LeadSource::where(self::SOURCE_DEALER_SITE)->delete();
        LeadSource::where(self::SOURCE_SERVICE_CAMPAIGN)->delete();
        LeadSource::where(self::SOURCE_PARTS_CAMPAIGN)->delete();
        LeadSource::where(self::SOURCE_OWNERSHIP_CAMPAIGN)->delete();
        LeadSource::where(self::SOURCE_FOLLOW_UP_CAMPAIGN)->delete();
    }
}
