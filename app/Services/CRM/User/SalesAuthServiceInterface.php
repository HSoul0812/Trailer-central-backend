<?php

namespace App\Services\CRM\User;

interface SalesAuthServiceInterface {
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
     * Return Response
     * 
     * @param AccessToken $accessToken
     * @param array $params
     * @return array
     */
    public function response($accessToken, $params);

    /**
     * Validate SMTP/IMAP Details
     * 
     * @param array $params {type: smtp|imap,
     *                       username: string,
     *                       password: string,
     *                       security: string (ssl|tls)
     *                       host: string
     *                       port: int}
     * @return bool
     */
    public function validate(array $params): bool;
}