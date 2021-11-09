<?php

namespace App\Services\CRM\User;

use App\Models\CRM\User\SalesPerson;
use App\Services\CRM\Email\DTOs\ConfigValidate;

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
     * Validate SMTP/IMAP
     * 
     * @param array $params {type: smtp|imap,
     *                       username: string,
     *                       password: string,
     *                       security: string (ssl|tls)
     *                       host: string
     *                       port: int}
     * @return ConfigValidate
     */
    public function validate(array $params): ConfigValidate;
}