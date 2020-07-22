<?php

namespace App\Traits;

use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Config;
use App\Models\CRM\User\SalesPerson;

trait MailHelper
{
    /**
     * @var array
     */
    protected $smtpConfig = [
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
     * Initialize User Mailer to Bind
     * 
     * @return Mailer
     */
    protected function getUserMailer() {
        // Fix SMTP Config
        $params = $this->validateConfig($this->smtpConfig);

        // Get SMTP Details
        $smtp_host = $params['smtp_host'];
        $smtp_port = $params['smtp_port'];
        $smtp_username = $params['smtp_username'];
        $smtp_password = $params['smtp_password'];
        $smtp_encryption = $params['smtp_encryption'];

        // Get From Details
        $from_email = $params['from_email'];
        $from_name = $params['from_name'];
        var_dump($params);
        die;

        // Create Swift SMTP Transport
        $transport = new \Swift_SmtpTransport($smtp_host, $smtp_port);
        $transport->setUsername($smtp_username);
        $transport->setPassword($smtp_password);
        $transport->setEncryption($smtp_encryption);

        // Create Swift Mailer
        $swift_mailer = new \Swift_Mailer($transport);

        // Create Mailer
        $mailer = new Mailer(app()->get('view'), $swift_mailer, app()->get('events'));
        $mailer->alwaysFrom($from_email, $from_name);
        $mailer->alwaysReplyTo($from_email, $from_name);

        // Return Mailer!
        return $mailer;
    }

    /**
     * Validate Config
     * 
     * @param array $config
     * @return array of set config, or default app config if set config is invalid
     */
    protected function validateConfig($config) {
        // If ANYTHING Important is Missing, Fallback to Defaults!
        if(empty($config['smtp_host']) || empty($config['smtp_port']) ||
           empty($config['smtp_username']) || empty($config['smtp_password']) ||
           empty($config['smtp_encryption'])) {
            // Set All Defaults!
            return [
                'smtp_host' => Config::get('mail.host'),
                'smtp_port' => Config::get('mail.post'),
                'smtp_username' => Config::get('mail.username'),
                'smtp_password' => Config::get('mail.password'),
                'smtp_encryption' => Config::get('mail.encryption'),
                'from_email' => Config::get('mail.from.address'),
                'from_name' => Config::get('mail.from.name')
            ];
        }

        // Only Uneeded Things are Empty!
        if(empty($config['from_email'])) {
            $config['from_email'] = $config['smtp_username'];
        }

        // Return Fallback Config
        return $config;
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
