<?php

namespace App\Console\Commands\CRM\Dms;

use App\Models\CRM\User\Customer;
use App\Models\CRM\User\Employee;
use App\Models\Parts\Vendor;
use App\Models\User\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class PrintDuplicatedDisplayNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:dms:print-duplicated-display-names {dealer_id : The dealer id that we want to run this command on.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Print the duplicated customers, vendors, and employees display name.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $dealer = User::findOrFail($this->argument('dealer_id'));
        
        $customers = Customer::where('dealer_id', $dealer->dealer_id)->get(['id', 'dealer_id', 'display_name']);
        $employees = Employee::where('dealer_id', $dealer->dealer_id)->get(['id', 'dealer_id', 'display_name']);
        $vendors = Vendor::where('dealer_id', $dealer)->get(['id', 'dealer_id', 'name']);
        
        $customerDisplayNames = $customers->pluck('display_name');
        /** @var Collection $displayNames */
        $displayNames = $customers->pluck('display_name')
            ->merge($employees->pluck('display_name'))
            ->merge($vendors->pluck('name'));
        
        $duplicatedDisplayNames = array_filter(array_count_values($displayNames->toArray()), function($value) {
            return $value > 1;
        });
        
        dd($duplicatedDisplayNames);
        
        return 0;
    }
}