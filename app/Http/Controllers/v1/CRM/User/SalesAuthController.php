<?php

namespace App\Http\Controllers\v1\CRM\User;

use App\Http\Controllers\RestfulController;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Utilities\Fractal\NoDataArraySerializer;
use Dingo\Api\Http\Request;
use App\Http\Requests\CRM\User\ShowSalesAuthRequest;
use App\Http\Requests\CRM\User\UpdateSalesAuthRequest;
use App\Transformers\Integration\Auth\TokenTransformer;
use App\Services\Integration\Auth\GoogleServiceInterface;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

class SalesAuthController extends RestfulController {

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
    public function show(Request $request)
    {
        // Handle Auth Sales People Request
        $request = new ShowSalesAuthRequest($request->all());
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
            if($params['token_type'] === 'google') {
                $validate = $this->google->validate($accessToken);
            }

            // Get Sales Person
            $salesPerson = $this->salesPerson->get([
                'sales_person_id' => $params['id']
            ]);
            $item = new Item($salesPerson, new SalesPersonTransformer(), 'data');
            $sales = $this->fractal->createData($item)->toArray();

            // Convert Token to Array
            $data = new Item($accessToken, new TokenTransformer(), 'data');
            $response = $this->fractal->createData($data)->toArray();
            $response['sales-person'] = $sales['data'];
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
            if($params['token_type'] === 'google') {
                $validate = $this->google->validate($accessToken);
            }

            // Get Sales Person
            $salesPerson = $this->salesPerson->update([
                'id' => $params['id'],
                'smtp_email' => $params['smtp_email']
            ]);
            $item = new Item($salesPerson, new SalesPersonTransformer(), 'data');
            $sales = $this->fractal->createData($item)->toArray();

            // Convert Token to Array
            $data = new Item($accessToken, new TokenTransformer(), 'data');
            $response = $this->fractal->createData($data)->toArray();
            $response['sales-person'] = $sales['data'];
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
    public function update(Request $request)
    {
        // Handle Auth Sales People Request
        $request = new UpdateSalesAuthRequest($request->all());
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
            if($params['token_type'] === 'google') {
                $validate = $this->google->validate($accessToken);
            }

            // Get Sales Person
            $salesPerson = $this->salesPerson->update([
                'id' => $params['id'],
                'smtp_email' => $params['smtp_email']
            ]);
            $item = new Item($salesPerson, new SalesPersonTransformer(), 'data');
            $sales = $this->fractal->createData($item)->toArray();

            // Convert Token to Array
            $data = new Item($accessToken, new TokenTransformer(), 'data');
            $response = $this->fractal->createData($data)->toArray();
            $response['sales-person'] = $sales['data'];
            $response['validate'] = $validate;

            // Return Auth
            return $this->response->array($response);
        }
    }
}
