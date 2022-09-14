<?php

namespace App\Transformers\CRM\User;

use App\Models\CRM\User\SalesPerson;
use App\Services\CRM\User\DTOs\SalesPersonConfig;
use App\Services\CRM\Email\ImapServiceInterface;
use App\Transformers\CRM\Email\ImapMailboxTransformer;
use App\Transformers\Dms\GenericSaleTransformer;
use App\Transformers\Integration\Facebook\ChatTransformer;
use App\Transformers\Pos\SaleTransformer;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;

class SalesPersonTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [];

    protected $availableIncludes = [
        'posSales',
        'allSales',
        'smtp',
        'imap',
        'folders',
        'defaultFolders',
        'authTypes',
        'facebookIntegrations',
    ];

    public function transform(SalesPerson $salesPerson): array
    {
        // Get SalesPersonConfig
        $config = new SalesPersonConfig();

        // Return Array
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
            'auth_config' => $salesPerson->auth_config,
            'auth_method' => $salesPerson->auth_method,
            'smtp_types' => $config->smtpTypes
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
                'failed' => !$salesPerson->smtp_validate->success,
                'message' => $salesPerson->smtp_validate->getMessage(),
                'error' => $salesPerson->smtp_error
            ];
        });
    }

    public function includeImap(SalesPerson $salesPerson)
    {
        return $this->item($salesPerson, function($salesPerson) {
            // Get Validate
            //$imapService = app()->make(ImapServiceInterface::class);
            //$validate = $imapService->validate($salesPerson->imap_config);

            // Return Results
            return [
                'email' => !empty($salesPerson->imap_email) ? $salesPerson->imap_email : $salesPerson->email,
                'password' => $salesPerson->imap_password,
                'host' => $salesPerson->imap_server,
                'port' => $salesPerson->imap_port,
                'security' => $salesPerson->imap_security,
                'failed' => false,//!$validate->success,
                'message' => $salesPerson->imap_validate->getMessage()
            ];
        });
    }

    /**
     * Transform Auth Types for Config
     * 
     * @return array
     */
    public function includeAuthTypes(SalesPerson $salesPerson) {
        // Get SalesPersonConfig
        $config = new SalesPersonConfig();
        return $this->collection($config->authTypes, new AuthTypeTransformer());
    }

    public function includeFolders(SalesPerson $salesPerson)
    {
        return $this->collection($salesPerson->folders, new EmailFolderTransformer());
    }

    public function includeDefaultFolders(SalesPerson $salesPerson)
    {
        /*$imapService = app()->make(ImapServiceInterface::class);
        $validate = $imapService->validate($salesPerson->imap_config);
        $folders = $validate->getDefaultFolders();*/
        $folders = new Collection();
        return $this->collection($folders, new ImapMailboxTransformer());
    }

    public function includePosSales(SalesPerson $salesPerson)
    {
        return $this->collection($salesPerson->posSales, new SaleTransformer());
    }

    public function includeAllSales(SalesPerson $salesPerson)
    {
        return $this->collection($salesPerson->allSales(), new GenericSaleTransformer());
    }

    public function includeFacebookIntegrations(SalesPerson $salesPerson)
    {
        $chatTransformer = app()->make(ChatTransformer::class);
        return $this->collection($salesPerson->facebookIntegrations, $chatTransformer);
    }
}
