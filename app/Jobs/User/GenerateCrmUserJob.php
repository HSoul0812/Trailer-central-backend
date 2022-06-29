<?php

namespace App\Jobs\User;

use App\Helpers\StringHelper;
use App\Jobs\Job;
use App\Models\User\User;
use App\Repositories\User\NewUserRepositoryInterface;
use App\Repositories\User\NewDealerUserRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\CRM\User\CrmUserRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class GenerateCrmUserJob
 * @package App\Jobs\CRM\User
 */
class GenerateCrmUserJob extends Job
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var User
     */
    private $user;

    /**
     * GenerateCrmUserJob constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param EmailBuilderServiceInterface $service
     * @throws EmailBuilderJobFailedException
     * @return boolean
     */
    public function handle(
        UserRepositoryInterface $users,
        NewUserRepositoryInterface $newUsers,
        NewDealerUserRepositoryInterface $newDealerUsers,
        CrmUserRepositoryInterface $crmUsers,
        StringHelper $stringHelper
    ) {
        // Initialize Logger
        $log = Log::channel('emailbuilder');
        $log->info('Processing ' . $this->leads->count() . ' Email Builder Emails', $this->config->getLogParams());

        try {
            $users->beginTransaction();

            // Create CRM User
            $newUser = $newUsers->create([
                'username' => $this->user->email,
                'email' => $this->user->email,
                'password' => $stringHelper->getRandomHex()
            ]);

            // Create Dealer User
            $newDealerUser = $newDealerUsers->create([
                'user_id' => $newUser->user_id,
                'salt' => $stringHelper->getRandomHex(),
                'auto_import_hide' => 0,
                'auto_msrp' => 0
            ]);
            $this->user->newDealerUser()->save($newDealerUser);

            // Create CRM User
            $crmUsers->create([
                'user_id' => $newUser->user_id,
                'logo' => '',
                'first_name' => '',
                'last_name' => '',
                'display_name' => '',
                'dealer_name' => $this->user->name,
                'active' => 0
            ]);

            $users->commitTransaction();

            Log::info('CRM user has been successfully generated', ['user_id' => $newDealerUser->user_id]);

            return true;
        } catch (\Exception $e) {
            Log::error("Generate CRM User error. dealer_id - {$this->user->dealer_id}", $e->getTrace());
            $users->rollbackTransaction();

            return false;
        }
    }
}
