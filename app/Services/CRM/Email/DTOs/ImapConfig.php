<?php

namespace App\Services\CRM\Email\DTOs;

use App\Models\CRM\User\SalesPerson;
use App\Models\CRM\User\EmailFolder;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;


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
     * @const default charset
     */
    const CHARSET_DEFAULT = 'UTF-8';

    /**
     * @const NTLM charset
     */
    const CHARSET_NTLM = 'US-ASCII';


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
    const NO_SSL_HOSTS = [143];


    /**
     * @const Folder Inbox
     */
    const FOLDER_INBOX = 'INBOX';


    /**
     * @const int SMTP Timeout
     */
    const DEFAULT_TIMEOUT = 1;


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
     * @return ImapConfig
     */
    public static function fillFromSalesPerson(SalesPerson $salesperson, EmailFolder $folder): ImapConfig {
        // Initialize
        $imapConfig = new self();

        // Set Username/Password
        $imapConfig->setUsername($salesperson->imap_email);
        $imapConfig->setPassword($salesperson->imap_password);

        // Set Host/Post
        $imapConfig->setHost($salesperson->imap_server);
        $imapConfig->setPort((int) $salesperson->imap_port ?? 0);
        $imapConfig->setSecurity($salesperson->imap_security ?: '');
        $imapConfig->setAuthType($salesperson->smtp_auth ?: '');
        $imapConfig->calcCharset();

        // Set Folder Config
        $imapConfig->setFolderName($folder->name);
        if(!empty($folder->date_imported)) {
            $imapConfig->setStartDate($folder->date_imported);
        } else {
            $imapConfig->setStartDate(Carbon::now()->sub(1, 'month'));
        }

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
        return $this->username;
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
        return $this->password;
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
        return $this->host;
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
        // 
        if($this->is)

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
        return $this->startDate;
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
     * Current IMAP Config Doesn't Append Any Security Settings?
     * 
     * @return bool
     */
    public function isNoSecurity(): bool {
        // Validate if Port is No Security
        return (!empty($this->port) && in_array($this->port, self::NO_SSL_PORTS));
    }
}