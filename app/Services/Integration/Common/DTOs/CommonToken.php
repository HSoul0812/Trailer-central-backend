<?php

namespace App\Services\Integration\Common\DTOs;

use Carbon\Carbon;
use App\Models\Integration\Auth\AccessToken;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;
use League\OAuth2\Client\Token\AccessToken as LeagueToken;

/**
 * Class CommonToken
 * 
 * @package App\Services\Integration\Common\DTOs
 */
class CommonToken
{
    use WithConstructor, WithGetter;

    /**
     * @var int Dealer ID
     */
    private $dealerId;

    /**
     * @var string Token Type
     */
    private $tokenType;

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
     * Fill Access Token From Array
     * 
     * @param array $authToken
     */
    public function fillFromArray(array $authToken) {
        // Fill Access Token
        $this->setAccessToken($authToken['access_token']);

        // Fill Refresh Token
        if(isset($authToken['refresh_token'])) {
            $this->setRefreshToken($authToken['refresh_token']);
        }

        // Fill ID Token
        $this->setIdToken($authToken['id_token']);

        // Fill Scopes
        $this->setScopes($authToken['scope']);

        // Fill Issued At
        if(isset($authToken['created'])) {
            $this->calcIssuedAt($authToken['created']);
        } else {
            $this->setIssuedAt($authToken['issued_at']);
        }

        // Fill Expires In
        $this->setExpiresIn($authToken['expires_in']);

        // Fill Expires At
        $this->calcExpiresAt($this->getIssuedAt(), $this->getExpiresIn());
    }

    /**
     * Fill Access Token From Array
     * 
     * @param LeagueToken $accessToken
     */
    public function fillFromLeague(LeagueToken $accessToken) {
        // Fill From League Access Token
        $this->accessToken = $accessToken->getToken();

        // Fill From League Refresh Token
        $this->refreshToken = $accessToken->getRefreshToken();

        // Fill From League ID Token
        $values = $accessToken->getValues();
        if(!empty($values['id_token'])) {
            $this->idToken = $values['id_token'];
        }

        // Fill From League Scope
        if(!empty($values['scope'])) {
            $this->setScopes($values['scope']);
        }

        // Fill From Expires In
        if(!empty($values['ext_expires_in'])) {
            $this->expiresIn = $values['ext_expires_in'];
        }

        // Calculate Issued At
        $this->calcIssuedAt($accessToken->getTimeNow());

        // Fill From League Refresh Token
        $this->expiresAt = Carbon::createFromTimestamp($accessToken->getExpires())->toDateTimeString();
    }

    /**
     * Fill CommonToken From Access Token
     * 
     * @param AccessToken $accessToken
     * @return CommonToken
     */
    public function fillFromToken(AccessToken $accessToken) {
        return new self([
            'access_token' => $accessToken->access_token,
            'refresh_token' => $accessToken->refresh_token,
            'id_token' => $accessToken->id_token,
            'expires_in' => $accessToken->expires_in,
            'created' => strtotime($accessToken->issued_at)
        ]);
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
        return $this->refreshToken ?? '';
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
     * Return Scope String
     * 
     * @return string implode(", ", $this->scopes)
     */
    public function getScope(): string
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
        return $this->expiresIn ?? 0;
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
     * Calculate Expires At
     * 
     * @param string $issuedAt
     * @param int $expiresIn
     * @return void
     */
    public function calcExpiresAt(string $issuedAt, int $expiresIn): void
    {
        // Calculate Expires At
        $this->expiresAt = Carbon::parse($issuedAt)->addSeconds($expiresIn)->toDateTimeString();
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
     * Return Issued At Unix
     * 
     * @return int $this->issuedAt
     */
    public function getIssuedUnix(): int
    {
        return Carbon::parse($this->issuedAt)->timestamp;
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
     * Calculate Issued At
     * 
     * @param string $issuedAt
     * @return void
     */
    public function calcIssuedAt(int $issuedAt): void
    {
        // Calculate Issued At
        $this->issuedAt = Carbon::createFromTimestamp($issuedAt)->toDateTimeString();
    }


    /**
     * Convert to Array for AccessToken
     * 
     * @param null|int $id
     * @param null|string $relationType
     * @param null|int $relationId
     * @return array{?id: null|int,
     *               access_token: string,
     *               refresh_token: string,
     *               id_token: string,
     *               expires_in: int,
     *               expires_at: string,
     *               issued_at: string}
     */
    public function toArray(?int $id = null, ?string $relationType = null, ?int $relationId = null): array {
        // Initialize Common Token Array
        $result = [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'id_token' => $this->idToken,
            'expires_in' => $this->expiresIn,
            'expires_at' => $this->expiresAt,
            'issued_at' => $this->issuedAt
        ];

        // Append ID
        if(!empty($id)) {
            $result['id'] = $id;
        }

        // Append Relation
        if(!empty($relationType) && !empty($relationId)) {
            $result['relation_type'] = $relationType;
            $result['relation_id'] = $relationId;
        }

        // Return Result Array
        return $result;
    }
}