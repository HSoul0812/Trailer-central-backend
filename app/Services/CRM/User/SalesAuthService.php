<?php

namespace App\Services\CRM\User;

use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Services\Integration\AuthServiceInterface;
use App\Utilities\Fractal\NoDataArraySerializer;
use App\Transformers\CRM\User\SalesPersonTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;

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
     * @var AuthServiceInterface
     */
    protected $auth;

    /**
     * @var Manager
     */
    private $fractal;

    /**
     * Construct Sales Auth Service
     */
    public function __construct(
        SalesPersonRepositoryInterface $salesPersonRepo,
        TokenRepositoryInterface $tokens,
        AuthServiceInterface $auth,
        Manager $fractal
    ) {
        $this->salesPerson = $salesPersonRepo;
        $this->tokens = $tokens;
        $this->auth = $auth;

        // Initialize Fractal
        $this->fractal = $fractal;
        $this->fractal->setSerializer(new NoDataArraySerializer());
    }

    /**
     * Show Sales Auth Response
     * 
     * @param array $params
     * @return Fractal
     */
    public function show(Request $request) {
        // Append Includes
        $this->fractal->setSerializer(new ArraySerializer());
        $this->fractal->parseIncludes($request->query('with', ''));

        // Adjust Request
        $params = $request->all();
        $params['relation_type'] = 'sales_person';
        $params['relation_id'] = $params['id'];
        unset($params['id']);

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
    public function create(Request $request) {
        // Append Includes
        $this->fractal->setSerializer(new ArraySerializer());
        $this->fractal->parseIncludes($request->query('with', ''));

        // Adjust Request
        $params = $request->all();
        $params['relation_type'] = 'sales_person';
        $params['relation_id'] = $params['id'];
        unset($params['id']);

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
    public function update(Request $request) {
        // Append Includes
        $this->fractal->setSerializer(new ArraySerializer());
        $this->fractal->parseIncludes($request->query('with', ''));

        // Adjust Request
        $params = $request->all();
        $params['relation_type'] = 'sales_person';
        $params['relation_id'] = $params['id'];
        unset($params['id']);

        // Create Access Token
        $accessToken = $this->tokens->update($params);

        // Return Response
        return $this->response($accessToken, $params);
    }


    /**
     * Return Response
     * 
     * @param AccessToken $accessToken
     * @param array $params
     * @return array
     */
    public function response($accessToken, $params) {
        // Get Sales Person
        $salesPerson = $this->salesPerson->get([
            'sales_person_id' => $params['relation_id']
        ]);
        $item = new Item($salesPerson, new SalesPersonTransformer(), 'sales_person');
        $response = $this->fractal->createData($item)->toArray();

        // Return Response
        return $this->auth->response($accessToken, $response);
    }
}
