<?php

namespace App\Traits;

use App\Models\CRM\User\SalesPerson;

trait MailHelper
{
    /**
     * @var array
     */
    public $smtpConfig = [
        'smtp_host'        => 'SMTP-HOST-HERE',
        'smtp_port'        => 'SMTP-PORT-HERE',
        'smtp_username'    => 'SMTP-USERNAME-HERE',
        'smtp_password'    => 'SMTP-PASSWORD-HERE',
        'smtp_encryption'  => 'SMTP-ENCRYPTION-HERE',
        'from_email'       => 'FROM-EMAIL-HERE',
        'from_name'        => 'FROM-NAME-HERE',
    ];

    /**
     * @param SalesPerson $salesPerson
     */
    public function setSalesPersonSmtpConfig(SalesPerson $salesPerson): void
    {
        // Set Config
        if (!empty($salesPerson->smtp_server)) {
            $this->smtpConfig = [
                'smtp_host'       => $salesPerson->smtp_server,
                'smtp_port'       => $salesPerson->smtp_port ?? '2525',
                'smtp_username'   => $salesPerson->smtp_email,
                'smtp_password'   => $salesPerson->smtp_password,
                'smtp_encryption' => $salesPerson->smtp_security ?? 'tls',
                'from_email'      => $salesPerson->smtp_email,
                'from_name'       => $salesPerson->full_name
            ];
        }
    }

    /**
     * Create a unique ID to use for boundaries.
     *
     * @return string
     */
    protected function generateId()
    {
        $len = 32; //32 bytes = 256 bits
        $bytes = '';
        if (function_exists('random_bytes')) {
            try {
                $bytes = random_bytes($len);
            } catch (\Exception $e) {
                //Do nothing
            }
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            /** @noinspection CryptographicallySecureRandomnessInspection */
            $bytes = openssl_random_pseudo_bytes($len);
        }
        if ($bytes === '') {
            //We failed to produce a proper random string, so make do.
            //Use a hash to force the length to the same as the other methods
            $bytes = hash('sha256', uniqid((string) mt_rand(), true), true);
        }

        //We don't care about messing up base64 format here, just want a random string
        return str_replace(['=', '+', '/'], '', base64_encode(hash('sha256', $bytes, true)));
    }

    /**
     * Get the server hostname.
     * Returns 'localhost.localdomain' if unknown.
     *
     * @return string
     */
    protected function serverHostname()
    {
        $result = '';
        if (!empty($this->Hostname)) {
            $result = $this->Hostname;
        } elseif (isset($_SERVER) && array_key_exists('SERVER_NAME', $_SERVER)) {
            $result = $_SERVER['SERVER_NAME'];
        } elseif (function_exists('gethostname') && gethostname() !== false) {
            $result = gethostname();
        } elseif (php_uname('n') !== false) {
            $result = php_uname('n');
        } else {
            return 'localhost.localdomain';
        }

        return $result;
    }
}
