<?php
namespace Tests\database\seeds\Website;

use App\Models\User\User;
use App\Models\Website\Website;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

class WebsiteSeeder extends Seeder {
    use WithGetter;

    /**
     * @var User $dealer
     */
    private $dealer;

    /**
     * @var Website $website
     */
    private $website;

    public function __construct() {}
    public function seed(): void
    {
        $this->dealer = factory(User::class)->create();
        $this->website = factory(Website::class)->create(['dealer_id' => $this->dealer->getKey()]);
    }
    public function cleanUp(): void
    {
        if(isset($this->dealer)) {
            User::destroy($this->dealer->getKey());
            Website::destroy($this->website->getKey());
        }
    }
}
