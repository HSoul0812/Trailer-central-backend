<?php

declare(strict_types=1);

namespace Tests\database\seeds\CRM\Leads;

use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\User\SalesPerson;
use App\Models\User\User;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * @property-read User $dealer
 * @property-read SalesPerson $sales
 * @property-read Lead $leads
 * @property-read array<LeadStatus> $unassignedLeads
 * @property-read array<LeadStatus> $salespeopleLeads
 */
class StatusSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var SalesPerson
     */
    private $sales;

    /**
     * @var Lead[]
     */
    private $leads;

    /**
     * @var LeadStatus[]
     */
    private $statuses = [];

    /**
     * InventorySeeder constructor.
     */
    public function __construct()
    {
        $this->dealer = factory(User::class)->create();
        $this->sales = factory(SalesPerson::class)->create(['user_id' => $this->dealer->user_id]);
    }

    public function seed(): void
    {
        $salesId = $this->sales->getKey();

        $seeds = [
            ['source' => ''],
            ['source' => 'Facebook - Podium'],
            ['source' => 'TrailerCentral'],
            ['source' => ''],
            ['source' => ''],
            ['source' => 'RVTrader.com', 'sales_id' => $salesId],
            ['source' => 'HorseTrailerWorld', 'sales_id' => $salesId],
            ['source' => '', 'sales_id' => $salesId]
        ];

        collect($seeds)->each(function (array $seed): void {
            $lead = factory(Lead::class)->create([
                'dealer_id' => $this->dealer->getKey()
            ]);
            $leadId = $lead->getKey();
            $this->leads[$leadId] = $lead;

            $status = factory(LeadStatus::class)->make([
                'tc_lead_identifier' => $leadId,
                'source' => $seed['source'],
                'sales_person_id' => $seed['sales_id'] ?? 0
            ]);

            $this->statuses[$leadId] = $status;
        });
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();
        $salesId = $this->sales->getKey();

        // Database clean up
        foreach($this->leads as $lead) {
            $leadId = $lead->identifier;
            LeadStatus::where('tc_lead_identifier', $leadId)->delete();
            Lead::destroy($leadId);
        }
        SalesPerson::destroy($salesId);
        User::destroy($dealerId);
    }
}
