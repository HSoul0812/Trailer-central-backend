<?php

namespace App\Console\Commands\CRM\Dms;

use App\Models\CRM\Dms\FinancingCompany;
use App\Models\CRM\Dms\Quickbooks\Account;
use App\Models\CRM\User\Customer;
use App\Models\CRM\User\Employee;
use App\Models\Parts\Vendor;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class TrimWhiteSpace
 *
 * @package App\Console\Commands\CRM\Dms
 */
class TrimWhiteSpace extends Command
{
    private const CHUNK_SIZE = 100;

    /**
     * @inheritDoc
     */
    protected $signature = 'crm:dms:trim-whitespace';

    /**
     * @inheritDoc
     */
    protected $description = 'Trim Whitespace from table fields.';

    public function handle()
    {
        $this->info('Processing Vendors.');
        $this->processVendors();
        $this->info('Processed Vendors.');

        $this->info('Processing Accounts.');
        $this->processAccounts();
        $this->info('Processed Accounts.');

        $this->info('Processing Employees.');
        $this->processEmployees();
        $this->info('Processed Employees.');

        $this->info('Processing Customers.');
        $this->processCustomers();
        $this->info('Processed Customers.');

        $this->info('Processing Financing Companies.');
        $this->processFinancingCompanies();
        $this->info('Processed Financing Companies.');

        return 0;
    }

    private function trim(Builder $query, string $functionName)
    {
        $query->chunk(self::CHUNK_SIZE, function (Collection $models) use ($functionName) {
            foreach ($models as $model) {
                DB::beginTransaction();

                try {
                    call_user_func([$this, $functionName], $model);

                    $model->timestamps = false;

                    $model->save();
                    DB::commit();
                } catch (Exception $exception) {
                    DB::rollBack();
                    $this->error($exception->getMessage());

                    return 1;
                }
            }
        });
    }

    private function processCustomers()
    {
        $this->trim(Customer::query(), 'processCustomerModel');
    }

    private function processCustomerModel(Customer $customer)
    {
        $customer->first_name .= '';
        $customer->last_name .= '';
        $customer->display_name .= '';
        $customer->middle_name .= '';
        $customer->company_name .= '';
    }

    private function processFinancingCompanies()
    {
        $this->trim(FinancingCompany::query(), 'processFinancingCompanyModel');
    }

    private function processFinancingCompanyModel(FinancingCompany $model)
    {
        $model->first_name .= '';
        $model->last_name .= '';
        $model->display_name .= '';
    }

    private function processAccounts()
    {
        $this->trim(Account::query(), 'processAccountModel');
    }

    private function processAccountModel(Account $model)
    {
        $model->name .= '';
    }

    private function processEmployees()
    {
        $this->trim(Employee::query(), 'processEmployeeModel');
    }

    private function processEmployeeModel(Employee $model)
    {
        $model->first_name .= '';
        $model->last_name .= '';
        $model->display_name .= '';
    }

    private function processVendors()
    {
        $this->trim(Vendor::query(), 'processVendorModel');
    }

    private function processVendorModel(Vendor $model)
    {
        $model->contact_name .= '';
        $model->name .= '';
    }
}
