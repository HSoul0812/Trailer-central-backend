<?php


namespace App\Transformers\Dms;


use App\Models\CRM\Dms\FinancingCompany;
use League\Fractal\TransformerAbstract;

class FinancingCompanyTransformer extends TransformerAbstract
{
    public function transform(FinancingCompany $financingCompany)
    {
        return [
            'id' => (int)$financingCompany->id, // int(10) unsigned NOT NULL AUTO_INCREMENT,
            // 'dealer_id' => $financingCompany->dealer_id, // int(10) unsigned NOT NULL,
            'first_name' => $financingCompany->first_name, // varchar(100) DEFAULT NULL,
            'last_name' => $financingCompany->last_name, // varchar(100) DEFAULT NULL,
            'display_name' => $financingCompany->display_name, // varchar(255) DEFAULT NULL,
            'email' => $financingCompany->email, // varchar(100) DEFAULT NULL,
            'drivers_license' => $financingCompany->drivers_license, // varchar(100) DEFAULT NULL,
            'home_phone' => $financingCompany->home_phone, // varchar(25) DEFAULT NULL,
            'work_phone' => $financingCompany->work_phone, // varchar(25) DEFAULT NULL,
            'cell_phone' => $financingCompany->cell_phone, // varchar(25) DEFAULT NULL,
            'address' => $financingCompany->address, // varchar(255) DEFAULT NULL,
            'city' => $financingCompany->city, // varchar(100) DEFAULT NULL,
            'region' => $financingCompany->region, // varchar(2) DEFAULT NULL,
            'postal_code' => $financingCompany->postal_code, // varchar(10) DEFAULT NULL,
            'country' => $financingCompany->country, // varchar(2) DEFAULT '',
            'tax_exempt' => $financingCompany->tax_exempt, // tinyint(1) NOT NULL DEFAULT '0',
            'account_number' => $financingCompany->account_number, // varchar(255) DEFAULT NULL,
            'gender' => $financingCompany->gender, // varchar(255) DEFAULT NULL,
            'dob' => $financingCompany->dob, // varchar(255) DEFAULT NULL,
            // 'payment_method_id' => $financingCompany->payment_method_id, // int(11) DEFAULT NULL,
            // 'qb_id' => $financingCompany->qb_id, // int(11) DEFAULT NULL,
            'created_at' => $financingCompany->created_at, // datetime DEFAULT NULL,
            'updated_at' => $financingCompany->updated_at, // datetime DEFAULT NULL,
            // 'deleted_at' => $financingCompany->deleted_at, // datetime DEFAULT NULL,
        ];
    }

}
