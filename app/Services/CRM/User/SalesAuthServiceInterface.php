<?php

namespace App\Services\CRM\User;

use App\Http\Requests\CRM\User\AuthorizeSalesAuthRequest;
use App\Models\Integration\Auth\AccessToken;
use App\Services\CRM\Email\DTOs\ConfigValidate;

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
     * Create Sales Person and Login
     * 
     * @param array $params
     * @return array{data: array<LoginTokenTransformer>,
     *               sales_person: array<SalesPersonTransformer>}
     */
    public function login(array $params): array;

    /**
     * Authorize Login With Code to Return Access Token
     * 
     * AuthorizeSalesAuthRequest $request
     * @return array{data: array<TokenTransformer>,
     *               sales_person: array<SalesPersonTransformer>}
     */
    public function authorize(AuthorizeSalesAuthRequest $request);

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

    /**
     * Return Response
     * 
     * @param int $salesPersonId
     * @param null|AccessToken $accessToken
     * @return array{sales_person: array<SalesPersonTransformer>,
     *               data: ?array<AccessToken>,
     *               validate: array<ValidateTokenTransformer>}
     */
    public function response(int $salesPersonId, ?AccessToken $accessToken = null): array;
}