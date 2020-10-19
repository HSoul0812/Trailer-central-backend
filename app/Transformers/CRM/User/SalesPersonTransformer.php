<?php

namespace App\Transformers\CRM\User;

use App\Transformers\Dms\GenericSaleTransformer;
use App\Transformers\Pos\SaleTransformer;
use League\Fractal\TransformerAbstract;
use App\Models\CRM\User\SalesPerson;
use App\Models\CRM\User\EmailFolder;

class SalesPersonTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'folders'
    ];

    protected $availableIncludes = [
        'posSales',
        'allSales'
    ];

    public function transform(SalesPerson $salesPerson)
    {
        return [
            'id' => $salesPerson->id,
            'user_id' => $salesPerson->user_id,
            'name' => $salesPerson->full_name,
            'first_name' => $salesPerson->first_name,
            'last_name' => $salesPerson->last_name,
            'perms' => $salesPerson->perms,
            'email' => $salesPerson->email,
            'is_default' => $salesPerson->is_default,
            'is_inventory' => $salesPerson->is_inventory,
            'is_financing' => $salesPerson->is_financing,
            'is_trade' => $salesPerson->is_trade,
            'signature' => $salesPerson->signature,
            'dealer_location_id' => $salesPerson->dealer_location_id,
            'smtp' => [
                'email' => !empty($salesPerson->smtp_email) ? $salesPerson->smtp_email : $salesPerson->email,
                'password' => $salesPerson->smtp_password,
                'host' => $salesPerson->smtp_server,
                'port' => $salesPerson->smtp_port,
                'security' => $salesPerson->smtp_security,
                'auth' => $salesPerson->smtp_auth,
                'failed' => $salesPerson->smtp_failed,
                'error' => $salesPerson->smtp_error
            ],
            'imap' => [
                'email' => !empty($salesPerson->imap_email) ? $salesPerson->imap_email : $salesPerson->email,
                'password' => $salesPerson->imap_password,
                'host' => $salesPerson->imap_server,
                'port' => $salesPerson->imap_port,
                'security' => $salesPerson->imap_security,
                'failed' => $salesPerson->imap_failed
            ]
        ];
    }

    public function includeFolders(SalesPerson $salesPerson)
    {
        return $this->collection($salesPerson->folders, new EmailFolderTransformer());
    }

    public function includePosSales(SalesPerson $salesPerson)
    {
        return $this->collection($salesPerson->posSales, new SaleTransformer());
    }

    public function includeAllSales(SalesPerson $salesPerson)
    {
        return $this->collection($salesPerson->allSales(), new GenericSaleTransformer());
    }
}
