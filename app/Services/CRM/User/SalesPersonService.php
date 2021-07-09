<?php

namespace App\Services\CRM\User;

use App\Models\CRM\User\SalesPerson;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Traits\SmtpHelper;

/**
 * Class SalesPersonService
 * 
 * @package App\Services\CRM\User
 */
class SalesPersonService implements SalesPersonServiceInterface
{
    use SmtpHelper;

    /**
     * @var SalesPersonRepository
     */
    protected $salespeople;

    /**
     * Construct Sales Auth Service
     */
    public function __construct(
        SalesPersonRepositoryInterface $salesPerson
    ) {
        $this->salespeople = $salesPerson;
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

        // Create Access Token
        $salesPerson = $this->salespeople->create($params);

        // Update Folders
        $this->updateFolders($salesPerson->id, $params['folders']);

        // Return Response
        return $this->salespeople->get(['id' => $salesPerson->id]);
    }

    /**
     * Update Sales Auth
     * 
     * @param array $rawParams
     * @return SalesPerson
     */
    public function update(array $rawParams): SalesPerson {
        // Merge SMTP/IMAP
        $params = $this->mergeSmtpImap($rawParams);

        // Update Sales Person
        $salesPerson = $this->salespeople->update($params);

        // Update Folders
        $this->updateFolders($salesPerson->id, $params['folders']);

        // Return Sales Person
        return $this->salespeople->get(['id' => $salesPerson->id]);
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
     * @return bool
     */
    public function validate(array $params): bool {
        // Get Smtp Config Details
        if($params['type'] === SalesPerson::TYPE_SMTP) {
            // Get SMTP Details
            $config = new SmtpConfig([
                'username' => $params['username'],
                'password' => $params['password'],
                'security' => $params['security'],
                'host' => $params['host'],
                'port' => $params['port']
            ]);

            // Validate SMTP Config
            return $this->validateSmtp($config);
        }

        // Return Response
        return false;
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
}
