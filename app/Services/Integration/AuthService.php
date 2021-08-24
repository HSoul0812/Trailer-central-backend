<?php

namespace App\Services\Integration;

use App\Exceptions\Integration\Auth\InvalidAuthLoginTokenTypeException;
use App\Exceptions\Integration\Auth\InvalidAuthCodeTokenTypeException;
use App\Http\Requests\Integration\Auth\LoginTokenRequest;
use App\Http\Requests\Integration\Auth\AuthorizeTokenRequest;
use App\Models\Integration\Auth\AccessToken;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Services\Integration\Common\DTOs\CommonToken;
use App\Services\Integration\Common\DTOs\EmailToken;
use App\Services\Integration\Common\DTOs\ValidateToken;
use App\Services\Integration\Facebook\BusinessServiceInterface;
use App\Services\Integration\Google\GoogleServiceInterface;
use App\Services\Integration\Google\GmailServiceInterface;
use App\Services\Integration\Microsoft\AzureServiceInterface;
use App\Services\Integration\Microsoft\OfficeServiceInterface;
use App\Utilities\Fractal\NoDataArraySerializer;
use App\Transformers\Integration\Auth\TokenTransformer;
use App\Transformers\Integration\Auth\LoginUrlTransformer;
use App\Transformers\Integration\Auth\ValidateTokenTransformer;
use Illuminate\Support\Facades\Log;
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
     * @var OfficeServiceInterface
     */
    protected $office;

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
        OfficeServiceInterface $office,
        Manager $fractal
    ) {
        $this->tokens = $tokens;
        $this->google = $google;
        $this->gmail = $gmail;
        $this->facebook = $facebook;
        $this->azure = $azure;
        $this->office = $office;

        // Fractal
        $this->fractal = $fractal;
        $this->fractal->setSerializer(new NoDataArraySerializer());

        // Initialize Logger
        $this->log = Log::channel('auth');
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
     * @param LoginTokenRequest $request
     * @throws InvalidAuthLoginTokenTypeException
     * @return array{url: string, ?state: string}
     */
    public function login(LoginTokenRequest $request): array {
        // Get Login URL's
        switch($request->token_type) {
            case 'google':
                $login = $this->google->login($request->redirect_uri, $request->scopes ?? []);
            break;
            case 'office365':
                $login = $this->office->login($request->redirect_uri, $request->scopes ?? []);
            break;
        }

        // Save State in Access Token Entry Temporarily
        if($login->authState) {
            $this->tokens->create([
                'token_type' => $request->token_type,
                'relation_type' => $request->relation_type,
                'relation_id' => $request->relation_id,
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
     * Handle Auth Code
     * 
     * @param string $tokenType
     * @param string $code
     * @param null|string $redirectUri
     * @param array $scopes
     * @throws InvalidAuthLoginTokenTypeException
     * @return EmailToken
     */
    public function code(string $tokenType, string $code, ?string $redirectUri = null, array $scopes = []): EmailToken {
        // Get Access Token
        switch($tokenType) {
            case 'google':
                $emailToken = $this->gmail->auth($code, $redirectUri);
            break;
            case 'office365':
                $emailToken = $this->office->auth($code, $redirectUri, $scopes);
            break;
        }

        // Email Token Empty?
        if(empty($emailToken)) {
            // Invalid Token Type
            throw new InvalidAuthCodeTokenTypeException;
        }

        // Return Email Token
        return $emailToken;
    }

    /**
     * Authorize Login and Retrieve Tokens
     * 
     * @param AuthorizeTokenRequest $request
     * @throws InvalidAuthCodeTokenTypeException
     * @return array<TokenTransformer>
     */
    public function authorize(AuthorizeTokenRequest $request): array {
        if(!empty($request->state)) {
            $stateToken = $this->tokens->getByState($request->state);
        }

        // Get Email Token
        $emailToken = $this->code($request->token_type, $request->auth_code,
                                    $request->redirect_uri, $request->scopes ?? []);

        // Create/Update Correct Access Token Details
        $accessToken = $this->tokens->create($emailToken->toArray($stateToken->id ?? null,
                            $request->token_type, $request->relation_type, $request->relation_id));

        // Return Response
        return $this->response($accessToken);
    }

    /**
     * Get Refresh Token
     * 
     * @param AccessToken $accessToken
     * @return null|CommonToken
     */
    public function refresh(AccessToken $accessToken): ?CommonToken {
        // Initialize Refresh Token
        $refresh = new CommonToken();

        // Validate Access Token
        switch($accessToken->token_type) {
            case 'google':
                $refresh = $this->google->refresh($accessToken);
            break;
            case 'office365':
                $refresh = $this->office->refresh($accessToken);
            break;
        }

        // Update Refresh Token
        if($refresh->exists()) {
            $this->log->info('Refreshed access token with ID #' . $accessToken->id . ' with replacement!');
            $this->tokens->refresh($accessToken->id, $refresh);
        }

        // Return Refresh Token
        return $refresh;
    }

    /**
     * Validate Access Token
     * 
     * @param AccessToken $accessToken
     * @return ValidateToken
     */
    public function validate(AccessToken $accessToken): ValidateToken {
        // Initialize Validate Token
        $validate = new ValidateToken();

        // Validate Access Token
        switch($accessToken->token_type) {
            case 'google':
                $validate = $this->google->validate($accessToken);
            break;
            case 'office365':
                $validate = $this->office->validate($accessToken);
            break;
        }

        // Update Refresh Token
        if($validate->newToken && $validate->newToken->exists()) {
            $this->log->info('Refreshed access token with ID #' . $accessToken->id . ' with replacement!');
            $accessToken = $this->tokens->refresh($accessToken->id, $validate->newToken);
            $validate->setAccessToken($accessToken);
        }

        // Return Validation
        return $validate;
    }

    /**
     * Validate Custom Access Token
     * 
     * @param CommonToken $accessToken general access token filled with data from request
     * @return ValidateToken
     */
    public function validateCustom(CommonToken $accessToken): ValidateToken {
        // Initialize Validate Token
        $validate = new ValidateToken();

        // Validate Access Token
        switch($accessToken->tokenType) {
            case 'google':
                $validate = $this->google->validateCustom($accessToken);
            break;
            case 'office365':
                $validate = $this->office->validateCustom($accessToken);
            break;
        }

        // Update Refresh Token
        if($validate->newToken && $validate->newToken->exists()) {
            $this->log->info('Refreshed access token with ID #' . $accessToken->id . ' with replacement!');
            $this->tokens->refresh($accessToken->id, $validate->newToken);
        }

        // Return Validation
        return $validate;
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
        $validation = new Item($this->validate($accessToken), new ValidateTokenTransformer(), 'validate');
        $validate = $this->fractal->createData($validation)->toArray();

        // Convert Token to Array
        if(!empty($accessToken)) {
            $data = new Item($accessToken, new TokenTransformer(), 'data');
            $token = $this->fractal->createData($data)->toArray();
        } else {
            $token = ['data' => null];
        }

        // Return Response
        return array_merge($response, $token, $validate);
    }
}
