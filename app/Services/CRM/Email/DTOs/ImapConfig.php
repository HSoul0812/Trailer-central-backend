<?php

namespace App\Services\CRM\Email\DTOs;

use App\Models\CRM\User\SalesPerson;
use App\Models\CRM\User\EmailFolder;
use App\Models\Integration\Auth\AccessToken;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;
use Carbon\Carbon;


/**
 * Class ImapConfig
 * 
 * @package App\Services\CRM\Email\DTOs
 */
class ImapConfig
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
     * @const string Auth IMAP
     */
    const AUTH_IMAP = 'IMAP';

    /**
     * @const default charset
     */
    const CHARSET_DEFAULT = 'UTF-8';

    /**
     * @const NTLM charset
     */
    const CHARSET_NTLM = 'US-ASCII';


    /**
     * @const string Auth Mode for XOAUTH (Gmail/Office 365)
     */
    const MODE_OAUTH = 'oauth';


    /**
     * @const No Certificate Suffix
     */
    const NO_CERT_SUFFIX = 'novalidate-cert';

    /**
     * @const No Valid Certificates
     */
    const NO_CERT_HOSTS = ['imap.gmail.com'];

    /**
     * @const No SSL By Default on These Ports
     */
    const NO_SSL_PORTS = [143];

    /**
     * @const Default Hosts By Auth Config
     */
    const DEFAULT_HOSTS = [
        'GMAIL' => 'imap.google.com',
        'OFFICE' => 'outlook.office365.com'
    ];

    /**
     * @const int Default Port
     */
    const DEFAULT_PORTS = [
        'ssl' => 993,
        'tls' => 143
    ];


    /**
     * @const int IMAP Timeout
     */
    const DEFAULT_TIMEOUT = 2;

    /**
     * @const Folder Inbox
     */
    const FOLDER_INBOX = 'INBOX';


    /**
     * @var string Username for IMAP
     */
    private $username;

    /**
     * @var string Password for IMAP
     */
    private $password;

    /**
     * @var string Host Name for IMAP
     */
    private $host;

    /**
     * @var int Port for IMAP Host
     */
    private $port;

    /**
     * @var string ssl || tls (Security Type for IMAP Connection)
     */
    private $security;

    /**
     * @var string Auth Type for IMAP Connection
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
     * @var string Charset for IMAP Connection
     */
    private $charset;

    /**
     * @var string Folder Name for IMAP Connection
     */
    private $folderName;

    /**
     * @var string Date to Start Importing From
     */
    private $startDate;
    

    /**
     * Get Smtp Config From Sales Person
     * 
     * @param SalesPerson $salesperson
     * @param null|EmailFolder $folder
     * @return ImapConfig
     */
    public static function fillFromSalesPerson(SalesPerson $salesperson, ?EmailFolder $folder = null): ImapConfig
    {
        // Get Start Date
        $startDate = Carbon::now()->sub(1, 'month');
        if(!empty($folder->date_imported)) {
            $startDate = $folder->date_imported;
        }

        // Return SmtpConfig
        $imapConfig = new self([
            'username' => $salesperson->imap_email,
            'password' => $salesperson->imap_password,
            'host' => $salesperson->imap_server,
            'port' => $salesperson->imap_port,
            'security' => $salesperson->imap_security,
            'auth_type' => $salesperson->smtp_auth,
            'access_token' => $salesperson->active_token,
            'folder_name' => !empty($folder->name) ? $folder->name : 'INBOX',
            'start_date' => $startDate
        ]);

        // Calc Charset
        $imapConfig->calcCharset();

        // Calc Auth Config From Access Token
        $imapConfig->calcAuthConfig();

        // Return IMAP Config
        return $imapConfig;
    }


    /**
     * Return Username
     * 
     * @return string $this->username
     */
    public function getUsername(): string
    {
        return $this->username ? trim($this->username) : '';
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
     * @return string XOAuth password || $this->password
     */
    public function getPassword(): string
    {
        // Are We OAuth?!
        if($this->isAuthConfigOauth()) {
            // Return XOAauth Password Instead!
            return $this->accessToken->access_token;
        }

        // Return Standard Password
        return $this->password ? trim($this->password) : '';
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
        // Host Exists?
        if($this->host) {
            return $this->host;
        }

        // Return Default!
        return self::DEFAULT_HOSTS[$this->authConfig] ?? '';
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
    public function getPort(): int
    {
        // Return Set Port
        if($this->port) {
            return $this->port;
        }

        // Return Default Port for Security
        $security = $this->getSecurity();
        return self::DEFAULT_PORTS[$security];
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
        // If No Security, Return Empty
        if($this->isNoSecurity()) {
            return '';
        }

        // Set Security Default
        $security = $this->security ?: self::SSL;

        // Append No Certificate on Gmail
        if($this->isNoCert()) {
            $security .= '/' . self::NO_CERT_SUFFIX;
        }

        // Return Security
        return $security;
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
     * @return string $this->authConfig
     */
    public function getAuthConfig(): string
    {
        return $this->authConfig ?? self::AUTH_IMAP;
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
        // Is Auth Type oAuth?!
        if($this->accessToken) {
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
        } elseif($this->authType === self::AUTH_NTLM) {
            // Auth Type is NTLM?
            $this->authConfig = self::AUTH_NTLM;
        } else {
            // Standard IMAP!
            $this->authConfig = self::AUTH_IMAP;
        }
    }


    /**
     * Return Charset
     * 
     * @return string $this->charset
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * Set Charset
     * 
     * @param string $charset
     * @return void
     */
    public function setCharset(string $charset): void
    {
        $this->charset = $charset;
    }

    /**
     * Determine Charset From Auth Type
     * 
     * @return void
     */
    public function calcCharset(): void
    {
        if($this->authType === 'NTLM') {
            $this->setCharset(self::CHARSET_NTLM);
        } else {
            $this->setCharset(self::CHARSET_DEFAULT);
        }
    }

    /**
     * Toggle Charset From One to the Other
     * 
     * @return void
     */
    public function toggleCharset(): void
    {
        if($this->charset === self::CHARSET_NTLM) {
            $this->setCharset(self::CHARSET_DEFAULT);
        } else {
            $this->setCharset(self::CHARSET_NTLM);
        }
    }


    /**
     * Return Folder Name
     * 
     * @return string $this->folderName
     */
    public function getFolderName(): string
    {
        return $this->folderName;
    }

    /**
     * Set Folder Name
     * 
     * @param string $folderName
     * @return void
     */
    public function setFolderName(string $folderName): void
    {
        $this->folderName = $folderName;
    }


    /**
     * Return Start Date
     * 
     * @return string $this->startDate
     */
    public function getStartDate(): string
    {
        return $this->startDate ?? 'days';
    }

    /**
     * Set Start Date
     * 
     * @param string $startDate
     * @return void
     */
    public function setStartDate(string $startDate): void
    {
        $this->startDate = $startDate;
    }


    /**
     * Current IMAP Config Appends No Certificate?
     * 
     * @return bool
     */
    public function isNoCert(): bool {
        // Validate if Host is No Certificate
        return (!empty($this->host) && in_array($this->host, self::NO_CERT_HOSTS));
    }

    /**
     * Get Credentials for IMAP From Config
     * 
     * @return array{host: string,
     *               port: int,
     *               encryption: string,
     *               validate_cert: bool,
     *               username: string,
     *               password: string,
     *               protocol: string,
     *               authentication: null|string,
     *               timeout: int}
     */
    public function getCredentials(): array {
        // Initialize Credentials
        return [
            'host'           => $this->getHost(),
            'port'           => $this->getPort(),
            'encryption'     => $this->getSecurity(),
            'validate_cert'  => true,
            'username'       => $this->getUsername(),
            'password'       => $this->getPassword(),
            'protocol'       => 'imap',
            'authentication' => $this->isAuthConfigOauth() ? self::MODE_OAUTH : null,
            'timeout'        => self::DEFAULT_TIMEOUT
        ];
    }

    /**
     * Current IMAP Config Doesn't Append Any Security Settings?
     * 
     * @return bool
     */
    public function isNoSecurity(): bool {
        // Validate if Port is No Security
        return (!empty($this->port) && in_array($this->port, self::NO_SSL_PORTS));
    }
}