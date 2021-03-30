<?php

namespace App\Traits;

use App\Models\CRM\User\SalesPerson;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use Illuminate\Support\Facades\Config;

trait MailHelper
{
    /**
     * @param SmtpConfig $smtpConfig
     */
    public function setSmtpConfig(SmtpConfig $smtpConfig): void
    {
        if (!empty($smtpConfig->getHost())) {
            $config = [
                'driver'        => 'smtp',
                'host'          => $smtpConfig->getHost(),
                'port'          => $smtpConfig->getPort() ?? '2525',
                'username'      => $smtpConfig->getUsername(),
                'password'      => $smtpConfig->getPassword(),
                'encryption'    => $smtpConfig->getSecurity(),
                'from'          => [
                    'address'   => $smtpConfig->getUsername(),
                    'name'      => $smtpConfig->getFromName()
                ]
            ];
            Config::set('mail', $config);
        }
    }

    /**
     * @param SalesPerson $salesPerson
     */
    public function setSalesPersonSmtpConfig(SalesPerson $salesPerson): void
    {
        if (!empty($salesPerson->smtp_server)) {
            $config = [
                'driver'        => 'smtp',
                'host'          => trim($salesPerson->smtp_server),
                'port'          => $salesPerson->smtp_port ?? '2525',
                'username'      => trim($salesPerson->smtp_email),
                'password'      => trim($salesPerson->smtp_password),
                'encryption'    => $salesPerson->smtp_security ?? 'tls',
                'from'          => [
                    'address'   => trim($salesPerson->smtp_email),
                    'name'      => $salesPerson->full_name
                ]
            ];
            Config::set('mail', $config);
        }
    }

    /**
     * Fix To Config
     * 
     * To Must be Array of Arrays or Just Email!
     * 
     * @param string|array $to
     * @return string|array
     */
    protected function getCleanTo($to) {
        // Is Array?
        if(is_array($to)) {
            // Only Single?
            if(isset($to['email'])) {
                // Validate Name!
                if(isset($to['name']) && empty($to['name'])) {
                    unset($to['name']);
                }

                // Return To As Array!
                return [$to];
            } else {
                // Loop To Array!
                foreach($to as $k => $v) {
                    // Remove if To Email Invalid!
                    if(!isset($v['email'])) {
                        unset($to[$k]);
                    }

                    // Remove Name if Name Empty!
                    if(isset($v['name']) && empty($v['name'])) {
                        unset($v['name']);
                        $to[$k] = $v;
                    }
                }
            }
        }

        // Return To As Normal!
        return $to;
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
