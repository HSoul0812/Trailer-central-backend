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
     * @const string Auth Outlook
     */
    const AUTH_OFFICE = 'OFFICE';

    /**
     * @const string Auth NTLM
     */
    const AUTH_NTLM = 'NTLM';

    /**
     * @const string Auth SMTP
     */
    const AUTH_SMTP = 'SMTP';

    /**
     * @const string Auth Mode for XOAUTH (Gmail/Office 365)
     */
    const MODE_OAUTH = 'XOAUTH2';


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
     * @var string Auth Config for IMAP Connection
     */
    private $authConfig;

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
    public static function fillFromSalesPerson(SalesPerson $salesperson): SmtpConfig
    {
        // Return SmtpConfig
        $smtpConfig = new self([
            'from_name' => $salesperson->full_name,
            'username' => $salesperson->smtp_email,
            'password' => $salesperson->smtp_password,
            'host' => $salesperson->smtp_server,
            'port' => $salesperson->smtp_port,
            'security' => $salesperson->smtp_security,
            'auth_type' => $salesperson->smtp_auth,
            'access_token' => $salesperson->active_token
        ]);

        // Calc Auth Config From Access Token
        $smtpConfig->calcAuthConfig();

        // Return IMAP Config
        return $smtpConfig;
    }


    /**
     * Return From Name
     * 
     * @return string $this->fromName
     */
    public function getFromName(): ?string
    {
        return trim($this->fromName);
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
        // Are We OAuth?!
        if($this->isAuthConfigOauth()) {
            // Return XOAauth Password Instead!
            return $this->accessToken->access_token;
        }

        // Return Standard Password
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
     * Return Auth Mode
     * 
     * @return string self::MODE_OAUTH || $this->getAuthType
     */
    public function getAuthMode(): string
    {
        // Are We OAuth?!
        if($this->isAuthConfigOauth()) {
            // Return XOAauth Password Instead!
            return self::MODE_OAUTH;
        }

        // Return Current Auth Type
        return $this->getAuthType();
    }

    /**
     * Is Auth Config Gmail?
     * 
     * @return bool $this->getAuthConfig() === self::AUTH_GMAIL
     */
    public function isAuthTypeGmail(): bool
    {
        return $this->getAuthConfig() === self::AUTH_GMAIL;
    }

    /**
     * Is Auth Config Office 365?
     * 
     * @return bool $this->getAuthConfig() === self::AUTH_OFFICE
     */
    public function isAuthTypeOffice(): bool
    {
        return $this->getAuthConfig() === self::AUTH_OFFICE;
    }

    /**
     * Is Auth Config NTLM?
     * 
     * @return bool $this->getAuthConfig() === self::AUTH_NTLM
     */
    public function isAuthTypeNtlm(): bool
    {
        return $this->getAuthConfig() === self::AUTH_NTLM;
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
     * Return Auth Configuration Type
     * 
     * @return string $this->authType
     */
    public function getAuthConfig(): string
    {
        return $this->authConfig ?? self::AUTH_SMTP;
    }

    /**
     * Return Auth Configuration Type
     * 
     * @return bool Access Token Exists
     */
    public function isAuthConfigOauth(): bool
    {
        if($this->accessToken) {
            return true;
        }
        return false;
    }

    /**
     * Determine Auth Config From Access Token
     * 
     * @return void
     */
    public function calcAuthConfig(): void
    {
        // Auth Type is NTLM?
        if($this->authType === self::AUTH_NTLM) {
            $this->authConfig = self::AUTH_NTLM;
        } else {
            // Token Type
            switch($this->accessToken->token_type) {
                case AccessToken::TOKEN_GOOGLE:
                    $this->authConfig = self::AUTH_GMAIL;
                break;
                case AccessToken::TOKEN_OFFICE:
                    $this->authConfig = self::AUTH_OFFICE;
                break;
                default:
                    $this->authConfig = self::AUTH_IMAP;
                break;
            }
        }
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