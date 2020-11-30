<?php

namespace App\Services\Integration;

use App\Exceptions\Integration\Auth\MissingAuthLoginTokenTypeScopesException;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Services\Integration\Google\GoogleServiceInterface;
use App\Utilities\Fractal\NoDataArraySerializer;
use App\Transformers\Integration\Auth\TokenTransformer;
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
     * @var Manager
     */
    private $fractal;

    /**
     * Construct Google Client
     */
    public function __construct(
        TokenRepositoryInterface $tokens,
        GoogleServiceInterface $google,
        Manager $fractal
    ) {
        $this->tokens = $tokens;
        $this->google = $google;
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
     * @param array $params
     * @return refresh token
     */
    public function login($params) {
        // Token Type and Scopes Required
        if(empty($params['token_type']) || empty($params['scopes'])) {
            throw new MissingAuthLoginTokenTypeScopesException;
        }

        // Initialize Login URL
        $auth = ['url' => null];

        // Get Login URL
        if($params['token_type'] === 'google') {
            // Auth Code Exists?!
            if(!empty($params['auth_code'])) {
                $auth = $this->google->auth($params['redirect_uri'], $params['auth_code']);
            } else {
                $login = $this->google->login($params['redirect_uri'], $params['scopes']);
                $auth = ['url' => $login];
            }
        }

        // Return Refresh Token
        return $auth;
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
     * @return array of validation
     */
    public function validate($accessToken) {
        // Initialize Access Token
        $validate = [
            'is_valid' => false,
            'is_expired' => true
        ];

        // Validate Access Token
        if(!empty($accessToken->token_type)) {
            if($accessToken->token_type === 'google') {
                $validate = $this->google->validate($accessToken);
            }
        }

        // Return Validation
        return $validate;
    }

    /**
     * Return Response
     * 
     * @param AccessToken $accessToken
     * @param array $response
     * @return array
     */
    public function response($accessToken, $response = array()) {
        // Set Validate
        $validate = $this->validate($accessToken);

        // Token Was Refreshed?!
        if(!empty($validate['new_token'])) {
            $time = time();
            $accessToken = $this->tokens->update([
                'id' => $accessToken->id,
                'access_token' => $validate['new_token']['access_token'],
                'id_token' => $validate['new_token']['id_token'],
                'expires_in' => $validate['new_token']['expires_in'],
                'expires_at' => date("Y-m-d H:i:s", $time + $validate['new_token']['expires_in']),
                'issued_at' => date("Y-m-d H:i:s", $time)
            ]);
        }

        // Convert Token to Array
        if(!empty($accessToken)) {
            $data = new Item($accessToken, new TokenTransformer(), 'data');
            $token = $this->fractal->createData($data)->toArray();
            $response['data'] = $token['data'];
        } else {
            $response['data'] = null;
        }

        // Set Validate
        unset($validate['new_token']);
        $response['validate'] = $validate;

        // Return Response
        return $response;
    }
}
