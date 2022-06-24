<?php

namespace App\Console\Commands\CRM\Dms;

use App\Models\CRM\User\Customer;
use App\Models\CRM\User\Employee;
use App\Models\Parts\Vendor;
use App\Models\User\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class PrintQuickbooksDuplicatedDisplayNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:dms:quickbooks:print-duplicated-display-names {dealer_id : The dealer id that we want to run this command on.}';

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
        
        /** @var Collection $customers */
        $customers = Customer::where('dealer_id', $dealer->dealer_id)->get(['id', 'dealer_id', 'display_name'])->groupBy('display_name');
        
        /** @var Collection $employees */
        $employees = Employee::where('dealer_id', $dealer->dealer_id)->get(['id', 'dealer_id', 'display_name'])->groupBy('display_name');
        
        /** @var Collection $vendors */
        $vendors = Vendor::where('dealer_id', $dealer->dealer_id)->get(['id', 'dealer_id', 'name'])->groupBy('name');
        
        /** @var Collection $displayNames */
        $displayNames = $customers->keys()
            ->merge($employees->keys())
            ->merge($vendors->keys());
        
        $duplicatedDisplayNames = array_filter(array_count_values($displayNames->toArray()), function($value) {
            return $value > 1;
        });
        
        $stats = collect([]);
        
        foreach ($duplicatedDisplayNames as $displayName => $dupTypeCount) {
            /** @var Collection $customersList */
            $customersList = $customers->get($displayName, collect([]));
            
            /** @var Collection $employeesList */
            $employeesList = $employees->get($displayName, collect([]));
            
            /** @var Collection $vendorsList */
            $vendorsList = $vendors->get($displayName, collect());
            
            $stats->push([
                'display_name' => $displayName,
                'customers' => $customersList->pluck('id'),
                'employees' => $employeesList->pluck('id'),
                'vendors' => $vendorsList->pluck('id'),
                'duplicated_count' => $customersList->count() + $employeesList->count() + $vendorsList->count(),
            ]);
        }
        
        $stats = $stats->sortByDesc('duplicated_count')->values();
        
        $this->info("Found {$stats->count()} duplicated display names!");
        
        if ($stats->isEmpty()) {
            $this->info("Hooray!");
            return 0;
        }
        
        foreach ($stats as $index => $stat) {
            $segments = collect([]);
            
            $no = $index + 1;
            
            $segments->push("$no. {$stat['display_name']}: {$stat['duplicated_count']}");
            
            foreach (['customers', 'employees', 'vendors'] as $modelType) {
                $typeTitle = ucfirst($modelType);
                
                if ($stat[$modelType]->isNotEmpty()) {
                    $segments->push("$typeTitle IDs: " . $stat[$modelType]->implode(', '));
                }
            }
            
            $this->info($segments->implode(' | '));
        }
        
        return 0;
    }
}