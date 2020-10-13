<?php

namespace App\Http\Controllers\v1\CRM\User;

use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Utilities\Fractal\NoDataArraySerializer;
use Dingo\Api\Http\Request;
use App\Http\Requests\CRM\User\ShowSalesAuthRequest;
use App\Http\Requests\CRM\User\UpdateSalesAuthRequest;
use App\Transformers\CRM\User\SalesPersonTransformer;
use App\Transformers\Integration\Auth\TokenTransformer;
use App\Services\Integration\Auth\GoogleServiceInterface;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

class SalesAuthController extends RestfulControllerV2 {

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

    public function __construct(
        SalesPersonRepositoryInterface $salesPersonRepo,
        TokenRepositoryInterface $tokens,
        GoogleServiceInterface $google,
        Manager $fractal
    ) {
        $this->middleware('setDealerIdOnRequest')->only(['create']);

        $this->salesPerson = $salesPersonRepo;
        $this->tokens = $tokens;
        $this->google = $google;
        $this->fractal = $fractal;

        $this->fractal->setSerializer(new NoDataArraySerializer());
    }

    /**
     * Get Sales Person and Access Token
     * 
     * @param Request $request
     * @return type
     */
    public function show(int $id, Request $request)
    {
        // Handle Auth Sales People Request
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new ShowSalesAuthRequest($requestData);
        if ($request->validate()) {
            // Adjust Request
            $params = $request->all();
            $params['relation_type'] = 'sales_person';
            $params['relation_id'] = $params['id'];
            unset($params['id']);

            // Get Access Token
            $accessToken = $this->tokens->getRelation($params);

            // Validate Access Token
            $validate = ['is_valid' => false];
            if(!empty($accessToken->token_type)) {
                if($accessToken->token_type === 'google') {
                    $validate = $this->google->validate($accessToken);
                }
            }

            // Get Sales Person
            $salesPerson = $this->salesPerson->get([
                'sales_person_id' => $params['relation_id']
            ]);
            $item = new Item($salesPerson, new SalesPersonTransformer(), 'sales-person');
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

            // Return Auth
            return $this->response->array($response);
        }
    }

    /**
     * Create Sales Person and Access Token
     * 
     * @param Request $request
     * @return type
     */
    public function create(Request $request)
    {
        // Handle Auth Sales People Request
        $request = new CreateSalesAuthRequest($request->all());
        if ($request->validate()) {
            // Adjust Request
            $params = $request->all();
            $params['relation_type'] = 'sales_person';
            $params['relation_id'] = $params['id'];
            unset($params['id']);

            // Create Access Token
            $accessToken = $this->tokens->create($params);

            // Validate Access Token
            $validate = ['is_valid' => false];
            if(!empty($accessToken->token_type)) {
                if($accessToken->token_type === 'google') {
                    $validate = $this->google->validate($accessToken);
                }
            }

            // Get Sales Person
            $salesPerson = $this->salesPerson->get([
                'sales_person_id' => $params['relation_id']
            ]);
            $item = new Item($salesPerson, new SalesPersonTransformer(), 'sales-person');
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

            // Return Auth
            return $this->response->array($response);
        }
    }

    /**
     * Update Sales Person and Access Token
     * 
     * @param Request $request
     * @return type
     */
    public function update(int $id, Request $request)
    {
        // Handle Auth Sales People Request
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new UpdateSalesAuthRequest($requestData);
        if ($request->validate()) {
            // Adjust Request
            $params = $request->all();
            $params['relation_type'] = 'sales_person';
            $params['relation_id'] = $params['id'];
            unset($params['id']);

            // Create Access Token
            $accessToken = $this->tokens->create($params);

            // Validate Access Token
            $validate = ['is_valid' => false];
            if(!empty($accessToken->token_type)) {
                if($accessToken->token_type === 'google') {
                    $validate = $this->google->validate($accessToken);
                }
            }

            // Get Sales Person
            $salesPerson = $this->salesPerson->get([
                'sales_person_id' => $params['relation_id']
            ]);
            $item = new Item($salesPerson, new SalesPersonTransformer(), 'sales-person');
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

            // Return Auth
            return $this->response->array($response);
        }
    }
}
