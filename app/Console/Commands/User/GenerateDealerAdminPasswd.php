<?php

namespace App\Console\Commands\User;

use App\Services\User\UserService;
use Illuminate\Console\Command;

class GenerateDealerAdminPasswd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tc:dealer-admin-passwd {dealerId} {passwd}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets the admin_passwd field of a dealer';

    /**
     * @var UserService
     */
    private $userService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(UserService $userService)
    {
        parent::__construct();
        $this->userService = $userService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        try {
            $dealerId = $this->argument('dealerId');
            $passwd = $this->argument('passwd');

            $this->userService->setAdminPasswd($dealerId, $passwd);
            $this->info('Admin password set');

            return true;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return false;
        }
    }
}
