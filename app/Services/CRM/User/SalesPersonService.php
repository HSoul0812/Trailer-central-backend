<?php

namespace App\Services\CRM\User;

use App\Exceptions\CRM\User\DuplicateChangeEmailSalesPersonException;
use App\Models\CRM\User\SalesPerson;
use App\Repositories\CRM\User\EmailFolderRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Services\CRM\Email\ImapServiceInterface;
use App\Services\CRM\Email\DTOs\ConfigValidate;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\CRM\Email\DTOs\ImapConfig;
use App\Traits\SmtpHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Class SalesPersonService
 * 
 * @package App\Services\CRM\User
 */
class SalesPersonService implements SalesPersonServiceInterface
{
    use SmtpHelper;

    /**
     * @var SalesPersonRepositoryInterface
     */
    protected $salespeople;

    /**
     * @var EmailFolderRepositoryInterface
     */
    protected $folders;

    /**
     * @var ImapServiceInterface
     */
    protected $imap;

    /**
     * Construct Sales Auth Service
     */
    public function __construct(
        SalesPersonRepositoryInterface $salesPerson,
        EmailFolderRepositoryInterface $folders,
        ImapServiceInterface $imap
    ) {
        $this->salespeople = $salesPerson;
        $this->folders = $folders;
        $this->imap = $imap;

        // Initialize Logger
        $this->log = Log::channel('auth');
    }

    /**
     * Create Sales Auth
     * 
     * @param array $rawParams
     * @return SalesPerson
     */
    public function create(array $rawParams): SalesPerson {
        // Merge SMTP/IMAP
        $params = $this->mergeSmtpImap($rawParams);

        // Find Existing Sales Person Email
        $existing = $this->salespeople->getByEmail($params['user_id'], $params['email']);
        if(!empty($existing->id)) {
            $params['id'] = $existing->id;
            return $this->update($params);
        }

        // Create Access Token
        $salesPerson = $this->salespeople->create($params);

        // Update Folders
        if(!empty($params['folders'])) {
            $this->updateFolders($salesPerson, $params['folders']);
        }

        // Return Response
        return $salesPerson;
    }

    /**
     * Update Sales Auth
     * 
     * @param array $rawParams
     * @throws DuplicateChangeEmailSalesPersonException
     * @return SalesPerson
     */
    public function update(array $rawParams): SalesPerson {
        // Merge SMTP/IMAP
        $params = $this->mergeSmtpImap($rawParams);

        // Find Existing Sales Person Email On a DIFFERENT Sales Person
        if(!empty($params['email'])) {
            $existing = $this->salespeople->getByEmail($params['user_id'], $params['email']);
            if(!empty($existing->id) && ($existing->id !== (int) $params['id'])) {
                // Has Been Deleted?
                $this->log->info('Got Existing sales person #' . $existing->id .
                                    ' that doesn\'t match updated sales person #' . $params['id'] .
                                    ' for email address ' . $params['email']);
                if(!empty($existing->deleted_at)) {
                    $this->salespeople->delete(['id' => $params['id']]);
                    $params['id'] = $existing->id;
                } else {
                    // Throw exception instead!
                    throw new DuplicateChangeEmailSalesPersonException;
                }
            }
        }

        // Update Sales Person
        $salesPerson = $this->salespeople->update($params);

        // Update Folders
        if(!empty($params['folders'])) {
            $this->updateFolders($salesPerson, $params['folders']);
        }

        // Return Sales Person
        return $salesPerson;
    }

    /**
     * Validate SMTP/IMAP Details
     * 
     * @param array $params {type: smtp|imap,
     *                       username: string,
     *                       password: string,
     *                       security: string (ssl|tls)
     *                       host: string
     *                       port: int}
     * @return ConfigValidate
     */
    public function validate(array $params): ConfigValidate {
        // Initialize Config Params
        $config = [
            'username' => $params['username'],
            'password' => $params['password'],
            'security' => $params['security'],
            'host' => $params['host'],
            'port' => $params['port']
        ];

        // Get Smtp Config Details
        if($params['type'] === SalesPerson::TYPE_SMTP) {
            // Validate SMTP Config
            return $this->validateSmtp(new SmtpConfig($config));
        }
        // Get Imap Config Details
        elseif($params['type'] === SalesPerson::TYPE_IMAP) {
            // Validate IMAP Config
            $imapConfig = new ImapConfig($config);
            $imapConfig->calcCharset();
            return $this->imap->validate($imapConfig);
        }

        // Return Response
        return new ConfigValidate([
            'type' => $params['type'],
            'success' => false
        ]);
    }


    /**
     * Merge SMTP/IMAP to Update Sales Person Details
     * 
     * @param array $params
     * @return array $params + {smtp_email: string,
     *                          smtp_password: string,
     *                          smtp_server: string,
     *                          smtp_port: int,
     *                          smtp_security: string,
     *                          smtp_auth: string,
     *                          smtp_failed: bool,
     *                          imap_email: string,
     *                          imap_password: string,
     *                          imap_server: string,
     *                          imap_port: int,
     *                          imap_security: string,
     *                          imap_failed: bool}
     */
    private function mergeSmtpImap(array $params): array {
        // Find SMTP Array
        $smtp = [
            'smtp_email' => '',
            'smtp_password' => '',
            'smtp_server' => '',
            'smtp_port' => 0,
            'smtp_security' => '',
            'smtp_auth' => '',
            'smtp_failed' => false
        ];
        if(isset($params['smtp'])) {
            foreach($params['smtp'] as $key => $val) {
                $smtp['smtp_' . $key] = $val;
            }
        }

        // Find IMAP Array
        $imap = [
            'imap_email' => '',
            'imap_password' => '',
            'imap_server' => '',
            'imap_port' => 0,
            'imap_security' => '',
            'imap_failed' => false
        ];
        if(isset($params['imap'])) {
            foreach($params['imap'] as $key => $val) {
                $imap['imap_' . $key] = $val;
            }
        }

        // Return Results
        return array_merge($params, $smtp, $imap);
    }

    /**
     * Update Folders for Sales Person
     * 
     * @param SalesPerson $salesPerson
     * @param array<{?id: int, name: string}> $folders
     * @return Collection<int>
     */
    private function updateFolders(SalesPerson $salesPerson, array $folders): Collection {
        // Folders Exist?
        $folderIds = [];
        foreach($folders as $folder) {
            $emailFolder = $this->folders->createOrUpdate([
                'id' => $folder['id'] ?? 0,
                'sales_person_id' => $salesPerson->id,
                'user_id' => $salesPerson->user_id,
                'name' => $folder['name']
            ]);
            $folderIds[] = $emailFolder->folder_id;
        }

        // Delete All Folders NOT Updated Here!
        $this->folders->deleteBulk($salesPerson->id, $folderIds);

        // Return Final Folder ID's
        return collect($folderIds);
    }
}
