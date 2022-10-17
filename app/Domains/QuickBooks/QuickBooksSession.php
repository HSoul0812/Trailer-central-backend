<?php

namespace App\Domains\QuickBooks;

class QuickBooksSession
{
    /** @var ?string */
    private $realmID;

    /** @var ?string */
    private $accessToken;

    /** @var ?string */
    private $refreshToken;

    /** @var ?string */
    private $expiresAt;

    public static function make(
        string $realmId,
        string $accessToken,
        string $refreshToken,
        string $expiresAt
    ): QuickbooksSession
    {
        $quickbooksSession = new QuickbooksSession();

        $quickbooksSession->realmID = $realmId;
        $quickbooksSession->accessToken = $accessToken;
        $quickbooksSession->refreshToken = $refreshToken;
        $quickbooksSession->expiresAt = $expiresAt;

        return $quickbooksSession;
    }

    public function getRealmID(): ?string
    {
        return $this->realmID;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function getAccessTokenExpiresAt(): ?string
    {
        return $this->expiresAt;
    }

    public function setRealmID($realmId): QuickbooksSession
    {
        $this->realmID = $realmId;

        return $this;
    }

    public function setAccessToken($accessToken): QuickbooksSession
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function setRefreshToken($refreshToken): QuickbooksSession
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function setAccessTokenExpiresAt($expiresAt): QuickbooksSession
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'realm_id' => $this->realmID,
            'expires_at' => $this->expiresAt,
        ];
    }
}

