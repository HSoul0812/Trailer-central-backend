<?php

namespace App\Services\CRM\User;

use Dingo\Api\Http\Request;

interface SalesAuthServiceInterface {
    /**
     * Show Sales Auth Response
     * 
     * @param array $params
     * @return Fractal
     */
    public function show(Request $request);

    /**
     * Create Sales Auth
     * 
     * @param array $params
     * @return Fractal
     */
    public function create(Request $request);

    /**
     * Update Sales Auth
     * 
     * @param array $params
     * @return Fractal
     */
    public function update(Request $request);


    /**
     * Return Response
     * 
     * @param AccessToken $accessToken
     * @param array $params
     * @return array
     */
    public function response($accessToken, $params);
}