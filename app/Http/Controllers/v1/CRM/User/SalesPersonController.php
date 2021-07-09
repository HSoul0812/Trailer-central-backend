<?php

namespace App\Http\Controllers\v1\CRM\User;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\CRM\User\GetSalesPeopleRequest;
use App\Http\Requests\CRM\User\ValidateSalesPeopleRequest;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Services\CRM\User\SalesPersonServiceInterface;
use App\Transformers\CRM\User\SalesPersonTransformer;
use App\Transformers\Reports\SalesPerson\SalesReportTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
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
     * @var SalesPersonServiceInterface
     */
    protected $salesService;

    /**
     * @var SalesPersonTransformer
     */
    private $salesPersonTransformer;

    /**
     * @var Manager
     */
    private $fractal;

    public function __construct(
        SalesPersonRepositoryInterface $salesPersonRepo,
        SalesPersonServiceInterface $salesPersonService,
        SalesPersonTransformer $salesPersonTransformer,
        Manager $fractal
    ) {
        $this->middleware('setDealerIdOnRequest')->only(['index', 'salesReport']);

        $this->salesPerson = $salesPersonRepo;
        $this->salesService = $salesPersonService;
        $this->salesPersonTransformer = $salesPersonTransformer;
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

    public function salesReport(Request $request)
    {
        $result = $this->salesPerson->salesReport($request->all());
        $data = new Item($result, new SalesReportTransformer(), 'data');

        $response = $this->fractal->createData($data)->toArray();
        return $this->response->array($response);
    }

    public function valid(Request $request): Response
    {
        $request = new ValidateSalesPeopleRequest($request->all());
        if ($request->validate()) {
            // Return Validation
            return $this->response->array([
                'data' => $this->salesService->validate($request->all())
            ]);
        }
        
        return $this->response->errorBadRequest();
    }
}
