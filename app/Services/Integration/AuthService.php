<?php

namespace App\Services\Integration;

use App\Exceptions\Integration\Auth\InvalidAuthLoginTokenTypeException;
use App\Exceptions\Integration\Auth\InvalidAuthCodeTokenTypeException;
use App\Models\Integration\Auth\AccessToken;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Services\Integration\Common\DTOs\CommonToken;
use App\Services\Integration\Common\DTOs\ValidateToken;
use App\Services\Integration\Facebook\BusinessServiceInterface;
use App\Services\Integration\Google\GoogleServiceInterface;
use App\Services\Integration\Google\GmailServiceInterface;
use App\Services\Integration\Microsoft\AzureServiceInterface;
use App\Utilities\Fractal\NoDataArraySerializer;
use App\Transformers\Integration\Auth\TokenTransformer;
use App\Transformers\Integration\Auth\LoginUrlTransformer;
use App\Transformers\Integration\Auth\ValidateTokenTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

/**
 * Class AuthService
 * 
 * @package App\Services\Integration
 */
class AuthService implements AuthServiceInterface
{
    /**
     * @var TokenRepository
     */
    protected $tokens;

    /**
     * @var GoogleServiceInterface
     */
    protected $google;

    /**
     * @var GmailServiceInterface
     */
    protected $gmail;

    /**
     * @var BusinessServiceInterface
     */
    protected $facebook;

    /**
     * @var AzureServiceInterface
     */
    protected $azure;

    /**
     * @var Manager
     */
    private $fractal;

    /**
     * Construct Google Client
     */
    public function __construct(
        TokenRepositoryInterface $tokens,
        GoogleServiceInterface $google,
        GmailServiceInterface $gmail,
        BusinessServiceInterface $facebook,
        AzureServiceInterface $azure,
        Manager $fractal
    ) {
        $this->tokens = $tokens;
        $this->google = $google;
        $this->gmail = $gmail;
        $this->facebook = $facebook;
        $this->azure = $azure;
        $this->fractal = $fractal;

        $this->fractal->setSerializer(new NoDataArraySerializer());
    }

    /**
     * Get Sales Auth Response
     * 
     * @param array $params
     * @return Fractal
     */
    public function index($params) {
        // Get Access Token
        $accessToken = $this->tokens->getRelation($params);

        // Return Response
        return $this->response($accessToken);
    }

    /**
     * Show Sales Auth Response
     * 
     * @param int $id
     * @return Fractal
     */
    public function show($id) {
        // Get Access Token
        $accessToken = $this->tokens->get(['id' => $id]);

        // Return Response
        return $this->response($accessToken);
    }

    /**
     * Create Sales Auth
     * 
     * @param array $params
     * @return Fractal
     */
    public function create($params) {
        // Create Access Token
        $accessToken = $this->tokens->create($params);

        // Return Response
        return $this->response($accessToken);
    }

    /**
     * Update Sales Auth
     * 
     * @param array $params
     * @return Fractal
     */
    public function update($params) {
        // Update Access Token
        $accessToken = $this->tokens->update($params);

        // Return Response
        return $this->response($accessToken);
    }


    /**
     * Get Login URL
     * 
     * @param string $tokenType
     * @param array $scopes
     * @param string $relationType
     * @param int $relationId
     * @param null|string $redirectUri
     * @throws InvalidAuthLoginTokenTypeException
     * @return array{url: string, ?state: string}
     */
    public function login(string $tokenType, array $scopes, string $relationType, int $relationId, ?string $redirectUri = null): array {
        // Get Login URL's
        switch($tokenType) {
            case 'google':
                $login = $this->google->login($redirectUri, $scopes);
            break;
            case 'office365':
                $login = $this->azure->login($redirectUri, $scopes);
            break;
        }

        // Save State in Access Token Entry Temporarily
        if($login->authState) {
            $this->tokens->create([
                'token_type' => $tokenType,
                'relation_type' => $relationType,
                'relation_id' => $relationId,
                'state' => $login->authState
            ]);
        }

        // Invalid Login URL Details
        if(empty($login)) {
            throw new InvalidAuthLoginTokenTypeException;
        }

        // Return Login Details
        $data = new Item($login, new LoginUrlTransformer(), 'data');
        return $this->fractal->createData($data)->toArray();
    }

    /**
     * Authorize Login and Retrieve Tokens
     * 
     * @param string $tokenType
     * @param string $code
     * @param null|string $state
     * @param null|string $redirectUri
     * @param null|array $scopes
     * @param null|string $relationType
     * @param null|int $relationId
     * @throws InvalidAuthCodeTokenTypeException
     * @return array<TokenTransformer>
     */
    public function authorize(string $tokenType, string $code, ?string $state = null, ?string $redirectUri = null,
                                ?array $scopes = null, ?string $relationType = null, ?int $relationId = null): array {
        // Find Saved State of Token
        if(!empty($state)) {
            $stateToken = $this->tokens->getByState($state);
        }   

        // Get Access Token
        switch($tokenType) {
            case 'google':
                $emailToken = $this->gmail->auth($redirectUri, $code);
            break;
            case 'office365':
                $emailToken = $this->azure->auth($code, $redirectUri, $scopes);
            break;
        }

        // Email Token Empty?
        if(empty($emailToken)) {
            // Invalid Token Type
            throw new InvalidAuthCodeTokenTypeException;
        }

        // Create/Update Correct Access Token Details
        echo $emailToken->getAccessToken() . PHP_EOL . PHP_EOL;
        $accessToken = $this->tokens->create($emailToken->toArray($stateToken->id ?? null, $tokenType, $relationType, $relationId));

        // Return Response
        return $this->response($accessToken);
    }

    /**
     * Get Refresh Token
     * 
     * @param array $params
     * @return refresh token
     */
    public function refresh($params) {
        // Initialize Refresh Token
        $refresh = null;

        // Find Refresh Token
        if(!empty($params['token_type'])) {
            if($params['token_type'] === 'google') {
                $refresh = $this->google->refresh($params);
            } elseif($params['token_type'] === 'facebook') {
                $refresh = $this->facebook->refresh($params);
            }
        }

        // Return Refresh Token
        return $refresh;
    }

    /**
     * Validate Access Token
     * 
     * @param AccessToken $accessToken
     * @return array{validate: <ValidateTokenTransformer>}
     */
    public function validate(AccessToken $accessToken): array {
        // Initialize Validate Token
        $validate = new ValidateToken();

        // Validate Access Token
        switch($accessToken->token_type) {
            case 'google':
                $validate = $this->google->validate($accessToken);
            break;
            case 'office365':
                $validate = $this->azure->validate($accessToken);
            break;
            case 'facebook':
                $validate = $this->facebook->validate($accessToken);
                unset($validate['refresh_token']);
                return ['validate' => $validate];
        }

        // Return Validation
        $data = new Item($validate, new ValidateTokenTransformer(), 'validate');
        return $this->fractal->createData($data)->toArray();
    }

    /**
     * Validate Custom Access Token
     * 
     * @param CommonToken $accessToken general access token filled with data from request
     * @return array{validate: <ValidateTokenTransformer>}
     */
    public function validateCustom(CommonToken $accessToken): array {
        // Initialize Validate Token
        $validate = new ValidateToken();

        // Validate Access Token
        switch($accessToken->tokenType) {
            case 'google':
                $validate = $this->google->validateCustom($accessToken);
            case 'office365':
                $validate = $this->azure->validateCustom($accessToken);
        }

        // Return Validation
        $data = new Item($validate, new ValidateTokenTransformer(), 'validate');
        return $this->fractal->createData($data)->toArray();
    }

    /**
     * Return Response
     * 
     * @param AccessToken $accessToken
     * @param array $response
     * @return array{data: array<TokenTransformer>,
     *               validate: array<ValidateTokenTransformer>}
     */
    public function response(AccessToken $accessToken, array $response = []): array {
        // Set Validate
        $validate = $this->validate($accessToken);
        if(!empty($validate['validate']['new_token'])) {
            $accessToken = $this->tokens->refresh($accessToken->id, $validate['validate']['new_token']);
        }
        unset($validate['validate']['new_token']);

        // Convert Token to Array
        if(!empty($accessToken)) {
            $data = new Item($accessToken, new TokenTransformer(), 'data');
            $response = $this->fractal->createData($data)->toArray();
        } else {
            $response = ['data' => null];
        }

        // Return Response
        return array($response, $validate);
    }
}
