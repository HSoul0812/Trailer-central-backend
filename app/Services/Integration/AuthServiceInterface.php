<?php

namespace App\Services\Integration;

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
     * @return array of validation
     */
    public function validate($accessToken);

    /**
     * Validate Custom Access Token
     * 
     * @param CommonToken $accessToken general access token filled with data from request
     * @return array of validation
     */
    public function validateCustom(CommonToken $accessToken);

    /**
     * Return Response
     * 
     * @param AccessToken $accessToken
     * @param array $validate
     * @return array
     */
    public function response($accessToken, $validate);
}