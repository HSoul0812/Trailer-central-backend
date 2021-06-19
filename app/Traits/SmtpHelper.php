<?php

namespace App\Traits;

use App\Models\CRM\User\SalesPerson;
use App\Services\CRM\Email\DTOs\ConfigValidate;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use Illuminate\Support\Facades\Log;

trait SmtpHelper
{
    /**
     * Validate SMTP From SmtpConfig
     * 
     * @param null|SmtpConfig $smtpConfig
     * @return ConfigValidate
     */
    public function validateSmtp(?SmtpConfig $smtpConfig): ConfigValidate
    {
        // Initialize as False
        $success = false;

        // SMTP Config Exists?
        if (!empty($smtpConfig) && $smtpConfig->host) {
            // Validate Smtp Via Swift Mailer
            $success = $this->validateSwiftSmtp($smtpConfig);
        }

        // Return Validate SMTP as False
        return new ConfigValidate([
            'type' => SalesPerson::TYPE_SMTP,
            'success' => $success
        ]);
    }

    /**
     * Validate SMTP From SalesPerson
     * 
     * @param SalesPerson $salesPerson
     * @return ConfigValidate
     */
    public function validateSalesPersonSmtp(SalesPerson $salesPerson): ConfigValidate
    {
        // Initialize as False
        $success = false;

        // Sales Person SMTP Service Exists?
        if (!empty($salesPerson->smtp_server)) {
            // Get Smtp Config From Sales Person
            $config = SmtpConfig::fillFromSalesPerson($salesPerson);

            // Validate Smtp Via Swift Mailer
            $success = $this->validateSwiftSmtp($config);
        }

        // Return Validate SMTP as False
        return new ConfigValidate([
            'type' => SalesPerson::TYPE_SMTP,
            'success' => $success
        ]);
    }


    /**
     * Validate SwiftMailer SMTP Config
     * 
     * @param SmtpConfig $config
     * @return bool
     */
    private function validateSwiftSmtp(SmtpConfig $config): bool {
        try {
            // Get Security Details
            $transport = new \Swift_SmtpTransport($config->host, $config->port, $config->security ?? null);
            $transport->setUsername($config->username);
            $transport->setPassword($config->password);
            $transport->setTimeout(SmtpConfig::DEFAULT_TIMEOUT);

            // Start Transport! If Invalid, an Exception Will be Thrown...
            $mailer = new \Swift_Mailer($transport);
            $mailer->getTransport()->start();

            // No Exception, Success!
            return true;
        }
        catch (\Swift_TransportException $e) {
            // Log Swift Mailer Error
            Log::error($e->getMessage());
            return false;
        }
    }
}
