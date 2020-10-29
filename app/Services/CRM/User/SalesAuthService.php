<?php

namespace App\Services\CRM\User;

use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Services\Integration\Auth\GoogleServiceInterface;
use App\Utilities\Fractal\NoDataArraySerializer;
use App\Transformers\CRM\User\SalesPersonTransformer;
use App\Transformers\Integration\Auth\TokenTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

/**
 * Class SalesAuthService
 * 
 * @package App\Services\CRM\User
 */
class SalesAuthService implements SalesAuthServiceInterface
{

    /**
     * @var SalesPersonRepository
     */
    protected $salesPerson;

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
        SalesPersonRepositoryInterface $salesPersonRepo,
        TokenRepositoryInterface $tokens,
        GoogleServiceInterface $google,
        Manager $fractal
    ) {
        $this->salesPerson = $salesPersonRepo;
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
        // Adjust Request
        $params['relation_type'] = 'sales_person';
        $params['relation_id'] = $params['id'];
        unset($params['id']);

        // Get Access Token
        $accessToken = $this->tokens->getRelation($params);

        // Validate Token
        $validate = $this->validate($accessToken);

        // Return Response
        return $this->response($accessToken, $params, $validate);
    }

    /**
     * Create Sales Auth
     * 
     * @param array $params
     * @return Fractal
     */
    public function create($params) {
        // Adjust Request
        $params['relation_type'] = 'sales_person';
        $params['relation_id'] = $params['id'];
        unset($params['id']);

        // Create Access Token
        $accessToken = $this->tokens->create($params);

        // Validate Token
        $validate = $this->validate($accessToken);

        // Return Response
        return $this->response($accessToken, $params, $validate);
    }

    /**
     * Update Sales Auth
     * 
     * @param array $params
     * @return Fractal
     */
    public function update($params) {
        // Adjust Request
        $params['relation_type'] = 'sales_person';
        $params['relation_id'] = $params['id'];
        unset($params['id']);

        // Create Access Token
        $accessToken = $this->tokens->update($params);

        // Validate Token
        $validate = $this->validate($accessToken);

        // Return Response
        return $this->response($accessToken, $params, $validate);
    }


    /**
     * Validate Access Token
     * 
     * @param AccessToken $accessToken
     * @return array of validation
     */
    private function validate($accessToken) {
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
     */
    private function response($accessToken, $params, $validate) {
        // Get Sales Person
        $salesPerson = $this->salesPerson->get([
            'sales_person_id' => $params['relation_id']
        ]);
        $item = new Item($salesPerson, new SalesPersonTransformer(), 'sales_person');
        $response = $this->fractal->createData($item)->toArray();

        // Convert Token to Array
        if(!empty($accessToken)) {
            $data = new Item($accessToken, new TokenTransformer(), 'data');
            $token = $this->fractal->createData($data)->toArray();
            $response['data'] = $token['data'];
        } else {
            $response['data'] = null;
        }
        $response['validate'] = $validate;

        // Return Response
        return $response;
    }
}
