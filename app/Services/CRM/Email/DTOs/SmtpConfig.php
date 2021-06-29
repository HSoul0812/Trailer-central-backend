<?php

namespace App\Services\CRM\Email\DTOs;

use App\Models\CRM\User\SalesPerson;
use App\Models\Integration\Auth\AccessToken;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;


/**
 * Class SmtpConfig
 * 
 * @package App\Services\CRM\Email\DTOs
 */
class SmtpConfig
{
    use WithConstructor, WithGetter;

    /**
     * @const string SSL
     */
    const SSL = 'ssl';

    /**
     * @const string TLS
     */
    const TLS = 'tls';

    /**
     * @const string Auth Auto
     */
    const AUTH_AUTO = 'auto';

    /**
     * @const string Auth Gmail
     */
    const AUTH_GMAIL = 'GMAIL';

    /**
     * @const string Auth NTLM
     */
    const AUTH_NTLM = 'NTLM';

    /**
     * @const string Auth SMTP
     */
    const AUTH_SMTP = 'SMTP';


    /**
     * @const int SMTP Timeout
     */
    const DEFAULT_TIMEOUT = 2;


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
     * Get Smtp Config From Sales Person
     * 
     * @param SalesPerson $salesperson
     * @return SmtpConfig
     */
    public static function fillFromSalesPerson(SalesPerson $salesperson): SmtpConfig {
        // Return SmtpConfig
        return new self([
            'from_name' => $salesperson->full_name,
            'username' => $salesperson->smtp_email,
            'password' => $salesperson->smtp_password,
            'host' => $salesperson->smtp_server,
            'port' => $salesperson->smtp_port,
            'security' => $salesperson->smtp_security,
            'auth_type' => !empty($salesperson->googleToken) ? self::AUTH_GMAIL : $salesperson->smtp_auth,
            'access_token' => $salesperson->googleToken
        ]);
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
    public function getAuthType(): ?string
    {
        return $this->authType;
    }

    /**
     * Return Auth Configuration Type
     * 
     * @return string $this->authType
     */
    public function getAuthConfig(): string
    {
        if($this->getAuthType() === self::AUTH_GMAIL ||
           $this->getAuthType() === self::AUTH_NTLM) {
            return $this->authType;
        }
        return self::AUTH_SMTP;
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
