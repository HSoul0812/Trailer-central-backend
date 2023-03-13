<?php

declare(strict_types=1);

namespace Tests\database\seeds\CRM\Email;

use App\Models\CRM\Email\Template;
use App\Models\CRM\User\SalesPerson;
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
 * @property-read array<Template> $createdTemplates
 * @property-read array<Template> $missingTemplates
 */
class TemplateSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Template[]
     */
    private $createdTemplates = [];

    /**
     * @var Template[]
     */
    private $missingTemplates = [];

    /**
     * InventorySeeder constructor.
     */
    public function __construct()
    {
        $this->dealer = factory(User::class)->create();
        $this->user = factory(NewUser::class)->create();
    }

    public function seed(): void
    {
        $seeds = [
            ['action' => 'create'],
            ['action' => 'create'],
            ['action' => 'create'],
            [],
            [],
            []
        ];

        collect($seeds)->each(function (array $seed): void {
            // Create Template
            if(isset($seed['action']) && $seed['action'] === 'create') {
                $template = factory(Template::class)->create([
                    'user_id' => $this->user->getKey()
                ]);

                $this->createdTemplates[] = $template;
                return;
            }

            // Make Template
            $template = factory(Template::class)->make([
                'user_id' => $this->user->getKey()
            ]);

            $this->missingTemplates[] = $template;
        });
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();
        $userId = $this->user->getKey();

        // Database clean up
        Template::where('user_id', $userId)->delete();
        SalesPerson::where('user_id', $userId)->forceDelete();
        NewUser::destroy($userId);
        DealerLocation::where('dealer_id', $dealerId)->delete();
        Website::where('dealer_id', $dealerId)->delete();                
        User::destroy($dealerId);
    }
}