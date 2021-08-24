<?php

namespace App\Services\Integration;

use App\Models\Integration\Auth\AccessToken;
use App\Services\Integration\Common\DTOs\AuthLoginPayload;
use App\Services\Integration\Common\DTOs\CommonToken;
use App\Services\Integration\Common\DTOs\EmailToken;
use App\Services\Integration\Common\DTOs\ValidateToken;
use App\Http\Requests\Integration\Auth\LoginTokenRequest;
use App\Http\Requests\Integration\Auth\AuthorizeTokenRequest;

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
     * @param AuthLoginPayload
     * @throws InvalidAuthLoginTokenTypeException
     * @return array{url: string, ?state: string}
     */
    public function login(AuthLoginPayload $payload): array;

    /**
     * Handle Auth Code
     * 
     * @param string $tokenType
     * @param string $code
     * @param null|string $redirectUri
     * @param array $scopes
     * @throws InvalidAuthLoginTokenTypeException
     * @return EmailToken
     */
    public function code(string $tokenType, string $code, ?string $redirectUri = null, array $scopes = []): EmailToken;

    /**
     * Authorize Login and Retrieve Tokens
     * 
     * @param AuthorizeTokenRequest $request
     * @throws InvalidAuthCodeTokenTypeException
     * @return array<TokenTransformer>
     */
    public function authorize(AuthorizeTokenRequest $request): array;

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