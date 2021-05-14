<?php

declare(strict_types=1);

namespace Tests\database\seeds\CRM\Email;

use App\Models\CRM\Email\Blast;
use App\Models\CRM\Email\BlastSent;
use App\Models\CRM\Email\Template;
use App\Models\CRM\Leads\Lead;
use App\Models\User\User;
use App\Models\User\NewUser;
use App\Models\User\DealerLocation;
use App\Models\Website\Website;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * @property-read User $dealer
 * @property-read Website $website
 * @property-read SalesPerson $sales
 * @property-read array<Lead> $leads
 * @property-read array<Blast> $blasts
 * @property-read array<BlastSent> $blastsSent
 * @property-read array<BlastSent> $blastsUnsent
 */
class BlastSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var Website
     */
    private $website;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Lead[]
     */
    private $leads;

    /**
     * @var Blasts[]
     */
    private $createdBlasts = [];

    /**
     * @var Blasts[]
     */
    private $missingBlasts = [];

    /**
     * @var BlastSent[]
     */
    private $blastsSent = [];

    /**
     * @var BlastSent[]
     */
    private $blastsUnsent = [];

    /**
     * InventorySeeder constructor.
     */
    public function __construct()
    {
        $this->dealer = factory(User::class)->create();
        $this->website = factory(Website::class)->create(['dealer_id' => $this->dealer->dealer_id]);
        $this->user = factory(NewUser::class)->create(['user_id' => $this->dealer->dealer_id]);
    }

    public function seed(): void
    {
        $seeds = [
            ['name' => 'Test Blast 1', 'action' => 'create'],
            ['name' => 'Test Blast 2', 'action' => 'create'],
            ['name' => 'Test Blast 3', 'action' => 'create'],
            ['name' => 'Test Blast 4'],
            ['name' => 'Test Blast 5'],
            ['name' => 'Test Blast 6'],
        ];

        collect($seeds)->each(function (array $seed): void {
            // Create Status
            if(isset($seed['action']) && $seed['action'] === 'create') {
                // Create Blast
                $blast = factory(Blast::class)->create([
                    'user_id' => $this->dealer->getKey(),
                    'campaign_name' => $seed['name'],
                    'campaign_subject' => $seed['subject'] ?? $seed['name']
                ]);

                $this->createdBlasts[] = $blast;
                return;
            }

            // Make Blast
            $blast = factory(Blast::class)->make([
                'user_id' => $this->dealer->getKey(),
                'campaign_name' => $seed['name'],
                'campaign_subject' => $seed['subject'] ?? $seed['name']
            ]);

            $this->missingBlasts[] = $blast;
        });


        // Create Sent Entries
        $sentSeeds = [
            ['action' => 'create'],
            ['action' => 'create'],
            ['action' => 'create'],
            [],
            [],
            []
        ];

        collect($sentSeeds)->each(function (array $seed): void {
            // Create Lead
            $lead = factory(Lead::class)->create();
            $this->leads[] = $lead;

            // Create Blast Sent
            if(isset($seed['action']) && $seed['action'] === 'create') {
                // Create Blast Sent
                $sent = factory(BlastSent::class)->create([
                    'email_blasts_id' => $this->blasts[0]->getKey(),
                    'lead_id' => $lead->getKey()
                ]);

                $this->blastsSent[] = $sent;
                return;
            }

            // Make Blast Sent
            $sent = factory(BlastSent::class)->create([
                'email_blasts_id' => $this->blasts[0]->getKey(),
                'lead_id' => $lead->getKey()
            ]);

            $this->blastsUnsent[] = $sent;
        });
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();

        // Database clean up
        if(!empty($this->createdBlasts) && count($this->createdBlasts)) {
            foreach($this->createdBlasts as $blast) {
                $blastId = $blast->email_blasts_id;
                BlastSent::where('email_blasts_id', $blastId)->delete();
                Blast::destroy($blastId);
            }
        }
        Template::where('user_id', $dealerId)->delete();
        Lead::where('dealer_id', $dealerId)->delete();
        SalesPerson::where('user_id', $dealerId)->delete();
        NewUser::destroy($dealerId);
        DealerLocation::where('dealer_id', $dealerId)->delete();
        Website::where('dealer_id', $dealerId)->delete();                
        User::destroy($dealerId);
    }
}