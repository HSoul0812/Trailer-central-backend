<?php

namespace App\Services\Integration\Common\DTOs;

use App\Models\Integration\Auth\AccessToken;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
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
    protected $dealerId;

    /**
     * @var string Token Type
     */
    protected $tokenType;

    /**
     * @var string Access Token
     */
    protected $accessToken;

    /**
     * @var string Refresh Token
     */
    protected $refreshToken;

    /**
     * @var string ID Token
     */
    protected $idToken;

    /**
     * @var array All Scopes Approved
     */
    protected $scopes;

    /**
     * @var int Expires In Seconds
     */
    protected $expiresIn;

    /**
     * @var string Expires At Date/Time
     */
    protected $expiresAt;

    /**
     * @var string Issued At Date/Time
     */
    protected $issuedAt;


    /**
     * Fill Access Token From Array
     * 
     * @param array $authToken
     * @return CommonToken
     */
    public static function fillFromArray(array $authToken): CommonToken {
        // Return Common Token
        $commonToken = new static([
            'access_token' => $authToken['access_token'],
            'refresh_token' => $authToken['refresh_token'] ?? '',
            'id_token' => $authToken['id_token'] ?? null,
            'scopes' => explode(" ", $authToken['scope']),
            'issued_at' => $authToken['issued_at'] ?? CarbonImmutable::now(),
            'expires_in' => $authToken['expires_in'],
            'expires_at' => $authToken['expires_at'] ?? ''
        ]);

        // Calculate Issued At
        if(!empty($authToken['created'])) {
            $commonToken->calcIssuedAt($authToken['created']);
        }

        // Calculate Expires At?
        if(empty($authToken['expires_at'])) {
            // Calculate Expires At Instead
            $commonToken->calcExpiresAt($commonToken->getIssuedAt(), $commonToken->getExpiresIn());
        }

        // Return Common Token
        return $commonToken;
    }

    /**
     * Fill Access Token From Array
     * 
     * @param LeagueToken $accessToken
     * @return CommonToken
     */
    public static function fillFromLeague(LeagueToken $accessToken): CommonToken {
        // Fill From League ID Token
        $values = $accessToken->getValues();

        // Return Common Token
        $commonToken = new static([
            'access_token' => $accessToken->getToken(),
            'refresh_token' => $accessToken->getRefreshToken(),
            'id_token' => $values['id_token'] ?? '',
            'scopes' => !empty($values['scope']) ? explode(" ", $values['scope']) : [],
            'expires_in' => $values['ext_expires_in'] ?? 0
        ]);

        // Calculate Issued At
        $issuedAt = Carbon::createFromTimestamp($accessToken->getTimeNow())->setTimezone('UTC')->timestamp;
        $commonToken->calcIssuedAt($issuedAt);

        // Fill From League Refresh Token
        $commonToken->setExpiresAt(Carbon::createFromTimestamp($accessToken->getExpires())->setTimezone('UTC')->toDateTimeString());

        // Return Common Token
        return $commonToken;
    }

    /**
     * Fill CommonToken From Access Token
     * 
     * @param AccessToken $accessToken
     * @return CommonToken
     */
    public static function fillFromToken(AccessToken $accessToken): CommonToken {
        return static::fillFromArray([
            'access_token' => $accessToken->access_token,
            'refresh_token' => $accessToken->refresh_token,
            'id_token' => $accessToken->id_token,
            'scope' => implode(" ", $accessToken->scope),
            'issued_at' => $accessToken->issued_at,
            'expires_at' => $accessToken->expired_at,
            'expires_in' => $accessToken->expires_in
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
     * Is Expired Now?
     * 
     * @return bool
     */
    public function isExpired(): bool
    {
        return (Carbon::parse($this->expiresAt)->timestamp > Carbon::now()->setTimezone('UTC')->timestamp);
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
     * @param null|string $tokenType
     * @param null|string $relationType
     * @param null|int $relationId
     * @param null|string $state
     * @return array{access_token: string,
     *               refresh_token: string,
     *               id_token: string,
     *               expires_in: int,
     *               expires_at: string,
     *               issued_at: string,
     *               scopes: array,
     *               ?id: null|int,
     *               ?token_type: null|string,
     *               ?relation_type: null|string,
     *               ?relation_id: null|int,
     *               ?state: null|string}
     */
    public function toArray(?int $id = null, ?string $tokenType = null, ?string $relationType = null,
            ?int $relationId = null, ?string $state = null): array {
        // Initialize Common Token Array
        $result = [
            'dealer_id' => $this->dealerId,
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'id_token' => $this->idToken,
            'expires_in' => $this->expiresIn,
            'expires_at' => $this->expiresAt,
            'issued_at' => $this->issuedAt,
            'scopes' => $this->scopes
        ];

        // Append ID
        if(!empty($id)) {
            $result['id'] = $id;
        }

        // Append Token Type
        if(!empty($tokenType)) {
            $result['token_type'] = $tokenType;
        }

        // Append Relation
        if(!empty($relationType) && !empty($relationId)) {
            $result['relation_type'] = $relationType;
            $result['relation_id'] = $relationId;
        }

        // Append State
        if(!empty($state)) {
            $result['state'] = $state;
        }

        // Return Result Array
        return $result;
    }

    /**
     * Token Exists?
     * 
     * @return bool
     */
    public function exists(): bool {
        // Access Token?
        if($this->accessToken) {
            return true;
        }
        return false;
    }
}