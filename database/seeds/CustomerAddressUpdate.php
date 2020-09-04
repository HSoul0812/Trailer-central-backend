<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\CRM\User\Customer;

class CustomerAddressUpdate extends Seeder
{
    /**
     * Set address to bill_to if does not exist.
     * 
     * @return void
     */
    public function run()
    {
        $customers = DB::table('dms_customer')
            ->whereRaw("qb_id IS NOT NULL AND NULLIF(bill_to, '') IS NOT NULL AND NULLIF(address, '') IS NULL")
            ->get();
        foreach ($customers as $item) {
            $customer = Customer::find($item->id);
            $customer->address = $customer->bill_to;
            $customer->save();
        }
    }
}
