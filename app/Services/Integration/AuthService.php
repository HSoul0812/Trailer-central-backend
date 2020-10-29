<?php

namespace App\Services\Integration;

use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Services\Integration\Auth\GoogleServiceInterface;
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
     * Show Sales Auth Response
     * 
     * @param array $params
     * @return Fractal
     */
    public function show($params) {
        // Get Access Token
        $accessToken = $this->tokens->getRelation($params);

        // Return Response
        return $this->response($accessToken, $params);
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
        return $this->response($accessToken, $params);
    }

    /**
     * Update Sales Auth
     * 
     * @param array $params
     * @return Fractal
     */
    public function update($params) {
        // Create Access Token
        $accessToken = $this->tokens->update($params);

        // Return Response
        return $this->response($accessToken, $params);
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
