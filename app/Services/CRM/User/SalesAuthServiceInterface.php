<?php

namespace App\Services\CRM\User;

use App\Models\Integration\Auth\AccessToken;

interface SalesAuthServiceInterface {
    /**
     * Show Sales Auth Response
     * 
     * @param array $params
     * @return array
     */
    public function show(array $params): array;

    /**
     * Create Sales Person and Auth
     * 
     * @param array $params
     * @return array
     */
    public function create(array $params): array;

    /**
     * Update Sales Auth
     * 
     * @param array $params
     * @return array
     */
    public function update(array $params): array;


    /**
     * Return Response
     * 
     * @param array $params
     * @param null|AccessToken $accessToken
     * @return array
     */
    public function response(array $params, ?AccessToken $accessToken = null): array;
}