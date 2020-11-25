<?php

namespace App\Services\Integration;

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
        $token = $this->tokens->create($params);

        // Get Refresh Token
        $refresh = $this->google->refresh($token);

        // Set Refresh Token
        $accessToken = $token;
        if(!empty($refresh)) {
            $accessToken = $this->tokens->update([
                'refresh_token' => $refresh
            ]);
        }

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
        $token = $this->tokens->update($params);

        // Get Refresh Token
        $refresh = $this->google->refresh($token);

        // Set Refresh Token
        $accessToken = $token;
        if(!empty($refresh)) {
            $accessToken = $this->tokens->update([
                'refresh_token' => $refresh
            ]);
        }

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
        // Convert Token to Array
        if(!empty($accessToken)) {
            $data = new Item($accessToken, new TokenTransformer(), 'data');
            $token = $this->fractal->createData($data)->toArray();
            $response['data'] = $token['data'];
        } else {
            $response['data'] = null;
        }

        // Set Validate
        $response['validate'] = $this->validate($accessToken);

        // Return Response
        return $response;
    }
}
