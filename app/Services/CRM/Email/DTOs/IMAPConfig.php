<?php

namespace App\Services\CRM\Email\DTOs;

/**
 * Class ImapConfig
 * 
 * @package App\Services\CRM\Email\DTOs
 */
class ImapConfig
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
     * @const default charset
     */
    const CHARSET_DEFAULT = 'UTF-8';

    /**
     * @const NTLM charset
     */
    const CHARSET_NTLM = 'US-ASCIII';


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
        return $this->fd;
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
     * @return string $this->fileAuth
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
}