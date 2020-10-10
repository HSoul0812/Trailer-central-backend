<?php

namespace App\Http\Controllers\v1\CRM\User;

use App\Http\Controllers\RestfulController;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Transformers\Reports\SalesPerson\SalesReportTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use Dingo\Api\Http\Request;
use App\Http\Requests\CRM\User\GetSalesPeopleRequest;
use App\Http\Requests\CRM\User\AuthSalesPeopleRequest;
use App\Transformers\CRM\User\SalesPersonTransformer;
use App\Services\Integration\Auth\GoogleServiceInterface;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class SalesPersonController extends RestfulController {

    /**
     * @var SalesPersonRepository
     */
    protected $salesPerson;

    /**
     * @var SalesPersonTransformer
     */
    private $salesPersonTransformer;

    /**
     * @var AccessTokenRepository
     */
    protected $auth;

    /**
     * @var GoogleServiceInterface
     */
    protected $gapiService;

    /**
     * @var Manager
     */
    private $fractal;

    public function __construct(
        SalesPersonRepositoryInterface $salesPersonRepo,
        SalesPersonTransformer $salesPersonTransformer,
        TokenRepositoryInterface $auth,
        GoogleServiceInterface $googleService,
        Manager $fractal
    ) {
        $this->auth = $auth;

        $this->middleware('setDealerIdOnRequest')->only(['index', 'auth', 'salesReport']);

        $this->salesPerson = $salesPersonRepo;
        $this->salesPersonTransformer = $salesPersonTransformer;
        $this->auth = $auth;
        $this->googleService = $googleService;
        $this->fractal = $fractal;

        $this->fractal->setSerializer(new NoDataArraySerializer());
    }

    public function index(Request $request)
    {
        $request = new GetSalesPeopleRequest($request->all());
        if ($request->validate()) {
            return $this->response->paginator($this->salesPerson->getAll($request->all()), new SalesPersonTransformer);
        }

        $this->fractal->parseIncludes($request->query('with', ''));

        /**
         * @var \Illuminate\Database\Eloquent\Collection $collection
         */
        $collection = $this->salesPerson
            ->withRequest($request)
            ->get([]);

        $data = new Collection($collection, $this->salesPersonTransformer, 'data');
        $data->setPaginator(new IlluminatePaginatorAdapter($this->salesPerson->getPaginator()));

        $response = (array)$this->fractal->createData($data)->toArray();
        return $this->response->array(
            $response
        );
    }

    /**
     * Validate Access Token for Sales Person
     * 
     * @param Request $request
     * @return type
     */
    public function token(Request $request)
    {
        // Handle Auth Sales People Request
        $request = new AuthSalesPeopleRequest($request->all());
        if ($request->validate()) {
            // Adjust Request
            $params = $request->all();
            $params['relation_type'] = 'sales_person';
            $params['relation_id'] = $params['id'];
            unset($params['id']);

            // Create Access Token
            $accessToken = $this->auth->create($params);

            // Validate Access Token
            $validate = ['is_valid' => false];
            if($params['token_type'] === 'google') {
                $validate = $this->googleService->validate($accessToken);
            }

            // Return Auth
            return $this->response->array([
                'data' => new Item($accessToken, new TokenTransformer(), 'data'),
                'validate' => $validate
            ]);
        }
    }

    public function salesReport(Request $request)
    {
        $result = $this->salesPerson->salesReport($request->all());
        $data = new Item($result, new SalesReportTransformer(), 'data');

        $response = $this->fractal->createData($data)->toArray();
        return $this->response->array($response);
    }
}
