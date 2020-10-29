<?php

namespace App\Services\Integration;

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
     * Return Response
     * 
     * @param AccessToken $accessToken
     * @param array $validate
     * @return array
     */
    public function response($accessToken, $validate);
}