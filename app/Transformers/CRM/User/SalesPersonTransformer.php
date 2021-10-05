<?php

namespace App\Transformers\CRM\User;

use App\Transformers\Dms\GenericSaleTransformer;
use App\Transformers\Pos\SaleTransformer;
use League\Fractal\TransformerAbstract;
use App\Models\CRM\User\SalesPerson;
use App\Models\CRM\User\EmailFolder;

class SalesPersonTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [];

    protected $availableIncludes = [
        'posSales',
        'allSales',
        'smtp',
        'imap',
        'folders'
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
            'dealer_location_id' => $salesPerson->dealer_location_id
        ];
    }

    public function includeSmtp(SalesPerson $salesPerson)
    {
        return $this->item($salesPerson, function($salesPerson) {
            return [
                'email' => !empty($salesPerson->smtp_email) ? $salesPerson->smtp_email : $salesPerson->email,
                'password' => $salesPerson->smtp_password,
                'host' => $salesPerson->smtp_server,
                'port' => $salesPerson->smtp_port,
                'security' => $salesPerson->smtp_security,
                'auth' => $salesPerson->smtp_auth,
                'failed' => !$salesPerson->smtp_validate,
                'error' => $salesPerson->smtp_error
            ];
        });
    }

    public function includeImap(SalesPerson $salesPerson)
    {
        return $this->item($salesPerson, function($salesPerson) {
            return [
                'email' => !empty($salesPerson->imap_email) ? $salesPerson->imap_email : $salesPerson->email,
                'password' => $salesPerson->imap_password,
                'host' => $salesPerson->imap_server,
                'port' => $salesPerson->imap_port,
                'security' => $salesPerson->imap_security,
                'failed' => $salesPerson->imap_validate
            ];
        });
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