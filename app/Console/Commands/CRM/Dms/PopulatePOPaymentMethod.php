<?php

namespace App\Console\Commands\CRM\Dms;

use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Console\Command;
use App\Models\CRM\Dms\Quickbooks\PaymentMethod;

class PopulatePOPaymentMethod extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:dms:populate-po-payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populates the PO payment method for dealers with DMS active';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(UserRepositoryInterface $userRepository)
    {
        $dmsActiveDealers = $userRepository->getDmsActiveUsers();
        
        foreach($dmsActiveDealers as $dealer) {
            if ( PaymentMethod::where('type', PaymentMethod::PAYMENT_METHOD_PO)->where('dealer_id', $dealer->dealer_id)->count() === 0) {
                try {
                    PaymentMethod::create([
                        'name' => 'PO',
                        'is_visible' => 1,
                        'is_default' => 0,
                        'type' => PaymentMethod::PAYMENT_METHOD_PO,
                        'dealer_id' => $dealer->dealer_id
                    ]); 
                } catch (\Exception $ex) {
                    $this->error("Could not add PO to {$dealer->dealer_id}");
                    continue;
                }               
               
               $this->info("Added PO to {$dealer->dealer_id}");
            }            
        }
        
    }
}
