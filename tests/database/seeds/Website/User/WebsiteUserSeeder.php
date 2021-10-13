<?php
namespace Tests\database\seeds\Website\User;

use App\Models\User\User;
use App\Models\Website\User\WebsiteUser;
use App\Models\Website\User\WebsiteUserToken;
use App\Models\Website\Website;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

class WebsiteUserSeeder extends Seeder {
    use WithGetter;

    /**
     * @var WebsiteUser
     */
    private $websiteUser;

    /**
     * @var Website $website
     */
    private $website;

    /**
     * @var User $dealer
     */
    private $dealer;

    /**
     * @var
     */
    private $password;

    public function __construct() {
        $this->password = '12345';
    }
    public function seed(): void
    {
        $this->dealer = factory(User::class)->create();
        $this->website = factory(Website::class)->create(['dealer_id' => $this->dealer->getKey()]);
        $this->websiteUser = factory(WebsiteUser::class)->create([
            'website_id' => $this->website->id,
            'password' => $this->password,
        ]);

        $this->websiteUser->token()->save(factory(WebsiteUserToken::class)->make());
    }
    public function cleanUp(): void
    {
        if(isset($this->dealer)) {
            User::destroy($this->dealer->getKey());
            Website::destroy($this->website->getKey());
            WebsiteUser::destroy($this->websiteUser->getKey());
        }
    }
}
