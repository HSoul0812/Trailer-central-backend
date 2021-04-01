<?php

namespace App\Services\CRM\Email\DTOs;

use App\Models\CRM\User\SalesPerson;
use App\Models\Integration\Auth\AccessToken;

/**
 * Class SmtpConfig
 * 
 * @package App\Services\CRM\Email\DTOs
 */
class SmtpConfig
{
    /**
     * @const string SSL
     */
    const SSL = 'ssl';

    /**
     * @const string TLS
     */
    const TLS = 'tls';

    /**
     * @const string Auth Gmail
     */
    const AUTH_GMAIL = 'GMAIL';

    /**
     * @const string Auth NTLM
     */
    const AUTH_NTLM = 'NTLM';


    /**
     * @var string From Name for SMTP
     */
    private $fromName;

    /**
     * @var string Username for SMTP
     */
    private $username;

    /**
     * @var string Password for SMTP
     */
    private $password;

    /**
     * @var string Host Name for SMTP
     */
    private $host;

    /**
     * @var int Port for SMTP Host
     */
    private $port;

    /**
     * @var string ssl || tls (Security Type for SMTP Connection)
     */
    private $security;

    /**
     * @var string Auth Type for SMTP Connection
     */
    private $authType;

    /**
     * @var string Access Token
     */
    private $accessToken;
    

    /**
     * Get Smtp Config From Provided Params
     * 
     * @param array $params
     */
    public function __construct(array $params = []) {
        // Variables Exist?
        if(!empty($params)) {
            // Check All Class Vars for Matches
            $vars = get_class_vars(get_class($this));
            foreach($vars as $var) {
                if(isset($params[$var])) {
                    $method = 'set' . ucfirst($var);
                    $this->{$method}($params[$var]);
                }
            }
        }
    }

    /**
     * Get Smtp Config From Sales Person
     * 
     * @param SalesPerson $salesperson
     * @return SmtpConfig
     */
    public static function fillFromSalesPerson(SalesPerson $salesperson): SmtpConfig {
        // Initialize
        $smtpConfig = new self();

        // Set Username/Password
        $smtpConfig->setFromName($salesperson->full_name);
        $smtpConfig->setUsername($salesperson->smtp_email);
        $smtpConfig->setPassword($salesperson->smtp_password);

        // Set Host/Post
        $smtpConfig->setHost($salesperson->smtp_server);
        $smtpConfig->setPort((int) $salesperson->smtp_port ?? 0);
        $smtpConfig->setSecurity($salesperson->smtp_security ?: '');
        $smtpConfig->setAuthType($salesperson->smtp_auth ?: '');

        // Return SMTP Config
        return $smtpConfig;
    }


    /**
     * Return From Name
     * 
     * @return string $this->fromName
     */
    public function getFromName(): ?string
    {
        return $this->fromName;
    }

    /**
     * Set From Name
     * 
     * @param string $fromName
     * @return void
     */
    public function setFromName(string $fromName): void
    {
        $this->fromName = $fromName;
    }


    /**
     * Return Username
     * 
     * @return string $this->username
     */
    public function getUsername(): string
    {
        return !empty($this->username) ? trim($this->username) : '';
    }

    /**
     * Set Username
     * 
     * @param string $username
     * @return void
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }


    /**
     * Return Password
     * 
     * @return string $this->password
     */
    public function getPassword(): string
    {
        return !empty($this->password) ? trim($this->password) : '';
    }

    /**
     * Set Password
     * 
     * @param string $password
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }


    /**
     * Return Host
     * 
     * @return string $this->host
     */
    public function getHost(): string
    {
        return !empty($this->host) ? trim($this->host) : '';
    }

    /**
     * Set Host
     * 
     * @param string $host
     * @return void
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }


    /**
     * Return Port
     * 
     * @return int $this->port
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * Set Port
     * 
     * @param int $port
     * @return void
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }


    /**
     * Return Security
     * 
     * @return string $this->security
     */
    public function getSecurity(): string
    {
        // Get Security Default
        return $this->security ?: self::SSL;
    }

    /**
     * Set Security
     * 
     * @param string $security
     * @return void
     */
    public function setSecurity(string $security): void
    {
        $this->security = $security;
    }


    /**
     * Return Auth Type
     * 
     * @return string $this->authType
     */
    public function getAuthType(): string
    {
        return $this->authType;
    }

    /**
     * Set Auth Type
     * 
     * @param string $authType
     * @return void
     */
    public function setAuthType(string $authType): void
    {
        $this->authType = $authType;
    }

    /**
     * Is Auth Type Gmail?
     * 
     * @return bool $this->getAuthType() === self::AUTH_GMAIL
     */
    public function isAuthTypeGmail(): bool
    {
        return $this->getAuthType() === self::AUTH_GMAIL;
    }

    /**
     * Is Auth Type NTLM?
     * 
     * @return bool $this->getAuthType === self::AUTH_NTLM
     */
    public function isAuthTypeNtlm(): bool
    {
        return $this->getAuthType() === self::AUTH_NTLM;
    }


    /**
     * Return Access Token
     * 
     * @return AccessToken $this->AccessToken
     */
    public function getAccessToken(): ?AccessToken
    {
        return $this->accessToken ?? null;
    }

    /**
     * Set Access Token
     * 
     * @param AccessToken $accessToken
     * @return void
     */
    public function setAccessToken(AccessToken $accessToken): void
    {
        $this->accessToken = $accessToken;
    }
}