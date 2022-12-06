<?php

namespace App\Console\Commands\CRM\Email;

use App\Repositories\CRM\Email\BounceRepositoryInterface;
use Illuminate\Console\Command;

class ParseBouncedEmailAddresses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:parse-bounced-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse any malformed email addresses in crm_email_bounces';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Resolve the repository and get all malformed bounces.
        $bounceRepository = resolve(BounceRepositoryInterface::class);
        $bounces = $bounceRepository->getAllMalformed();
        $count = count($bounces);

        // If we have no malformed addresses, there is no point in continuing.
        if ($count == 0) {
            $this->info("No malformed addresses found.");
            return;
        }

        // Let the user know we found X malformed bounces.
        $this->info("$this->signature found $count bounces to be updated.");

        // Let's make sure we give an option to exit in case the count is not the expected one.
        if (!$this->confirm('Are you sure you want to update the bounces?')) {
            $this->info("Either the command was cancelled or there were no malformed email addresses");
            return;
        }

        // Everything looks fine, let's do it!
        $updated = 0;
        foreach ($bounces as $bounce) {
            $oldEmail = $bounce->email_address;
            $bounce->email_address = $bounceRepository->parseEmail($bounce->email_address);
            $bounceRepository->update($bounce->toArray());
            $this->info("The email '$oldEmail' was updated with: '$bounce->email_address'. for email_bounce_id: $bounce->email_bounce_id");
            $updated++;
        }

        // Show the result
        $this->info("$this->signature updated $updated malformed bounces.");
    }
}
