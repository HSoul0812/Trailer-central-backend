<?php

namespace App\Services\Integration;

use App\Models\Integration\Auth\AccessToken;
use App\Services\Integration\Common\DTOs\CommonToken;
use App\Services\Integration\Common\DTOs\EmailToken;
use App\Services\Integration\Common\DTOs\ValidateToken;

interface AuthServiceInterface {
    /**
     * Show Sales Auth Response
     * 
     * @param array $params
     * @return Fractal
     */
    public function show($params);

    /**
     * Create Sales Auth
     * 
     * @param array $params
     * @return Fractal
     */
    public function create($params);

    /**
     * Update Sales Auth
     * 
     * @param array $params
     * @return Fractal
     */
    public function update($params);

    /**
     * Get Login URL
     * 
     * @param string $tokenType
     * @param string $relationType
     * @param int $relationId
     * @param null|array $scopes
     * @param null|string $redirectUri
     * @throws InvalidAuthLoginTokenTypeException
     * @return array{url: string, ?state: string}
     */
    public function login(string $tokenType, string $relationType, int $relationId,
                          ?array $scopes = null, ?string $redirectUri = null): array;

    /**
     * Handle Auth Code
     * 
     * @param string $tokenType
     * @param string $code
     * @param null|string $redirectUri
     * @param null|array $scopes
     * @throws InvalidAuthLoginTokenTypeException
     * @return EmailToken
     */
    public function code(string $tokenType, string $code, ?string $redirectUri = null, ?array $scopes = null): EmailToken;

    /**
     * Authorize Login and Retrieve Tokens
     * 
     * @param string $tokenType
     * @param string $code
     * @param null|string $state
     * @param null|string $redirectUri
     * @param null|array $scopes
     * @param null|string $relationType
     * @param null|int $relationId
     * @throws InvalidAuthCodeTokenTypeException
     * @return array<TokenTransformer>
     */
    public function authorize(string $tokenType, string $code, ?string $state = null, ?string $redirectUri = null,
                                ?array $scopes = null, ?string $relationType = null, ?int $relationId = null): array;

    /**
     * Get Refresh Token
     * 
     * @param AccessToken $accessToken
     * @return null|CommonToken
     */
    public function refresh(AccessToken $accessToken): ?CommonToken;


    /**
     * Validate Access Token
     * 
     * @param AccessToken $accessToken
     * @return ValidateToken
     */
    public function validate(AccessToken $accessToken): ValidateToken;

    /**
     * Validate Custom Access Token
     * 
     * @param CommonToken $accessToken general access token filled with data from request
     * @return ValidateToken
     */
    public function validateCustom(CommonToken $accessToken): ValidateToken;

    /**
     * Return Response
     * 
     * @param AccessToken $accessToken
     * @param array $response
     * @return array{data: array<TokenTransformer>,
     *               validate: array<ValidateTokenTransformer>}
     */
    public function response(AccessToken $accessToken, array $response = []): array;
}