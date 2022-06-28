<?php

namespace App\Console\Commands\User;

use Illuminate\Console\Command;
use App\Helpers\StringHelper;
use App\Repositories\User\NewUserRepository;
use App\Repositories\User\NewDealerUserRepository;
use App\Repositories\CRM\User\CrmUserRepositoryInterface;
use App\Models\User\User;

class GenerateCrmUsers extends Command{
    
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "user:generate-crm-users";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates CRM Users on all Dealers missing them.';

    /**
     * @var NewUserRepository
     */
    private $newUsers;

    /**
     * @var NewDealerRepository
     */
    private $newDealerUsers;

    /**
     * @var CrmUserRepository
     */
    private $crmUsers;

    /**
     * @var StringHelper
     */
    private $stringHelper;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        NewUserRepository $newUsers,
        NewDealerUserRepository $newDealerUsers,
        CrmUserRepositoryInterface $crmUsers,
        StringHelper $stringHelper
    ) {
        parent::__construct();

        $this->newUsers = $newUsers;
        $this->newDealerUsers = $newDealerUsers;
        $this->crmUsers = $crmUsers;
        $this->stringHelper = $stringHelper;
    }
    
    public function handle() 
    {
        // Get All Users
        $addCount = 0;
        $users = User::doesntHave('dealerUsers')->get();
        foreach ($users as $user) {
            // Create CRM User
            $newUser = $this->newUsers->create([
                'username' => $user->email,
                'email' => $user->email,
                'password' => $this->stringHelper->getRandomHex()
            ]);

            // Create Dealer User
            $newDealerUser = $this->newDealerUsers->create([
                'user_id' => $newUser->user_id,
                'salt' => $this->stringHelper->getRandomHex(),
                'auto_import_hide' => 0,
                'auto_msrp' => 0
            ]);
            $user->newDealerUser()->save($newDealerUser);

            // Create CRM User
            $this->crmUsers->create([
                'user_id' => $newUser->user_id,
                'logo' => '',
                'first_name' => '',
                'last_name' => '',
                'display_name' => '',
                'dealer_name' => $user->name,
                'active' => 0
            ]);

            // Get Auth Token for User!
            if (!empty($newDealerUser->user_id)) {
                $addCount++;
            }
        }
        echo "Added {$addCount} CRM users for dealers without crm users".PHP_EOL;
    }
}

