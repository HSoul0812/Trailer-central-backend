<?php

namespace App\Traits;

use App\Models\CRM\User\SalesPerson;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use Illuminate\Support\Facades\Log;

trait SmtpHelper
{
    /**
     * Validate SMTP From SmtpConfig
     * 
     * @param null|SmtpConfig $smtpConfig
     * @return bool
     */
    public function validateSmtp(?SmtpConfig $smtpConfig): bool
    {
        // SMTP Config Exists?
        if (!empty($smtpConfig) && $smtpConfig->host) {
            // Validate Smtp Via Swift Mailer
            return $this->validateSwiftSmtp($smtpConfig);
        }

        // Return Validate SMTP as False
        return false;
    }

    /**
     * Validate SMTP From SalesPerson
     * 
     * @param SalesPerson $salesPerson
     * @return bool
     */
    public function validateSalesPersonSmtp(SalesPerson $salesPerson): bool
    {
        // Sales Person SMTP Service Exists?
        if (!empty($salesPerson->smtp_server)) {
            // Get Smtp Config From Sales Person
            $config = SmtpConfig::fillFromSalesPerson($salesPerson);

            // Validate Smtp Via Swift Mailer
            return $this->validateSwiftSmtp($config);
        }

        // Return Validate SMTP as False
        return false;
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
            $transport = \Swift_SmtpTransport::newInstance($config->host, $config->port, $config->security ?? null);
            $transport->setUsername($config->username);
            $transport->setPassword($config->password);

            // Start Transport! If Invalid, an Exception Will be Thrown...
            $mailer = \Swift_Mailer::newInstance($transport);
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
