<?php

namespace App\Services\Integration\Google\DTOs;

/**
 * Class GoogleToken
 * 
 * @package App\Services\Integration\Google\DTOs
 */
class GoogleToken
{
    /**
     * @var string Access Token
     */
    private $accessToken;

    /**
     * @var string Refresh Token
     */
    private $refreshToken;

    /**
     * @var string ID Token
     */
    private $idToken;

    /**
     * @var array All Scopes Approved
     */
    private $scopes;

    /**
     * @var int Expires In Seconds
     */
    private $expiresIn;

    /**
     * @var string Expires At Date/Time
     */
    private $expiresAt;

    /**
     * @var string Issued At Date/Time
     */
    private $issuedAt;

    /**
     * @var string Email Address Approved For
     */
    private $emailAddress;


    /**
     * Fill Access Token From Array
     * 
     * @param stdclass $authToken
     */
    public function fillFromArray(\stdclass $authToken) {
        // Fill Access Token
        $this->setAccessToken($authToken->access_token);

        // Fill Refresh Token
        $this->setRefreshToken($authToken->refresh_token);

        // Fill ID Token
        $this->setIdToken($authToken->id_token);

        // Fill Scopes
        $this->setScopes($authToken->scopes);

        // Fill Expires In
        $this->setExpiresIn($authToken->expires_in);

        // Fill Expires At
        $this->setExpiresAt($authToken->expires_at);

        // Fill Issued At
        $this->setIssuedAt($authToken->issued_at);
    }


    /**
     * Return Temp Access Token
     * 
     * @return string $this->accessToken
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * Set Temp Access Token
     * 
     * @param string $accessToken
     * @return void
     */
    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }


    /**
     * Return Refresh Token
     * 
     * @return string $this->refreshToken
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * Set Refresh Token
     * 
     * @param string $refreshToken
     * @return void
     */
    public function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }


    /**
     * Return ID Token
     * 
     * @return string $this->idToken
     */
    public function getIdToken(): string
    {
        return $this->idToken;
    }

    /**
     * Set ID Token
     * 
     * @param string $idToken
     * @return void
     */
    public function setIdToken(string $idToken): void
    {
        $this->idToken = $idToken;
    }


    /**
     * Return Scopes
     * 
     * @return array $this->scopes
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * Return Scopes String
     * 
     * @return string implode(", ", $this->scopes)
     */
    public function getScopesString(): string
    {
        return implode(" ", $this->scopes);
    }

    /**
     * Set Scopes
     * 
     * @param string $scopes
     * @return void
     */
    public function setScopes(string $scopes): void
    {
        $this->scopes = explode(" ", $scopes);
    }


    /**
     * Return Expires In
     * 
     * @return int $this->expiresIn
     */
    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }

    /**
     * Set Expires In
     * 
     * @param int $expiresIn
     * @return void
     */
    public function setExpiresIn(int $expiresIn): void
    {
        $this->expiresIn = $expiresIn;
    }


    /**
     * Return Expires At
     * 
     * @return string $this->expiresAt
     */
    public function getExpiresAt(): string
    {
        return $this->expiresAt;
    }

    /**
     * Set Expires At
     * 
     * @param string $expiresAt
     * @return void
     */
    public function setExpiresAt(string $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }


    /**
     * Return Issued At
     * 
     * @return string $this->issuedAt
     */
    public function getIssuedAt(): string
    {
        return $this->issuedAt;
    }

    /**
     * Set Issued At
     * 
     * @param string $issuedAt
     * @return void
     */
    public function setIssuedAt(string $issuedAt): void
    {
        $this->issuedAt = $issuedAt;
    }


    /**
     * Return Email Address
     * 
     * @return string $this->emailAddress
     */
    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    /**
     * Set Email Address
     * 
     * @param string $emailAddress
     * @return void
     */
    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }
}