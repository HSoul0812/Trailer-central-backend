<?php

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadSource;
use App\Models\CRM\Leads\LeadStatus;
use Illuminate\Database\Migrations\Migration;

class ConvertDealersLeadSources extends Migration
{
    /** @const array */
    public const SOURCE_PAST_CUSTOMER = ["user_id" => 0, "source_name" => "Past Customer"];

    /** @const array */
    public const SOURCE_DRIVE_BY = ["user_id" => 0, "source_name" => "Drive By"];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Handle Past Customer
        $this->createDealerSource(self::SOURCE_PAST_CUSTOMER['source_name']);

        // Handle Drive By
        $this->createDealerSource(self::SOURCE_DRIVE_BY['source_name']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Handle the creation of Past Customer as source for
     * dealers that have already used it.
     * @param $source
     */
    private function createDealerSource($source)
    {
        $query = LeadStatus::query();

        $query->select([
            LeadStatus::TABLE_NAME . '.tc_lead_identifier',
            LeadStatus::TABLE_NAME . '.source',
            Lead::TABLE_NAME . '.dealer_id'
        ])->leftJoin(
            Lead::TABLE_NAME,
            LeadStatus::TABLE_NAME . '.tc_lead_identifier',
            '=',
            Lead::TABLE_NAME . '.identifier'
        )->where(
            LeadStatus::TABLE_NAME . '.source',
            $source
        )->whereNotNull(
            Lead::TABLE_NAME . '.dealer_id'
        )->groupBy(Lead::TABLE_NAME . '.dealer_id');

        $results = $query->get();

        foreach ($results as $result) {
            LeadSource::create([
                'user_id' => $result->dealer_id,
                'source_name' => $source
            ]);
        }
    }
}
