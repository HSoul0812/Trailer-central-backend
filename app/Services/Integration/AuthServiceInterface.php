<?php

namespace App\Services\Integration;

use App\Models\Integration\Auth\AccessToken;
use App\Services\Integration\Common\DTOs\CommonToken;

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
     * Validate Access Token
     * 
     * @param AccessToken $accessToken
     * @return array{validate: <ValidateTokenTransformer>}
     */
    public function validate(AccessToken $accessToken): array;

    /**
     * Validate Custom Access Token
     * 
     * @param CommonToken $accessToken general access token filled with data from request
     * @return array{validate: <ValidateTokenTransformer>}
     */
    public function validateCustom(CommonToken $accessToken): array;

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