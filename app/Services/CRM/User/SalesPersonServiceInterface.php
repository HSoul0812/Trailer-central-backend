<?php

namespace App\Services\CRM\User;

interface SalesPersonServiceInterface {
    /**
     * Create Sales Auth
     * 
     * @param array $rawParams
     * @return SalesPerson
     */
    public function create(array $rawParams): SalesPerson;
    /**
     * Update Sales Auth
     * 
     * @param array $rawParams
     * @return SalesPerson
     */
    public function update(array $rawParams): SalesPerson;

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