<?php

namespace App\Http\Controllers\v1\CRM\User;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\CRM\User\ConfigSalesPeopleRequest;
use App\Http\Requests\CRM\User\GetSalesPeopleRequest;
use App\Http\Requests\CRM\User\GetSalesPersonRequest;
use App\Http\Requests\CRM\User\ValidateSalesPeopleRequest;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Services\CRM\User\DTOs\SalesPersonConfig;
use App\Services\CRM\User\SalesPersonServiceInterface;
use App\Transformers\CRM\Email\ConfigValidateTransformer;
use App\Transformers\CRM\User\SalesPersonTransformer;
use App\Transformers\CRM\User\SalesPersonConfigTransformer;
use App\Transformers\Reports\SalesPerson\SalesReportTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use App\Http\Requests\CRM\User\DeleteSalesPersonRequest;

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
     * @var SalesPersonConfigTransformer
     */
    private $salesPersonConfigTransformer;

    /**
     * @var ConfigValidateTransformer
     */
    private $emailConfigTransformer;

    /**
     * @var Manager
     */
    private $fractal;

    public function __construct(
        SalesPersonRepositoryInterface $salesPersonRepo,
        SalesPersonServiceInterface $salesPersonService,
        SalesPersonTransformer $salesPersonTransformer,
        SalesPersonConfigTransformer $salesPersonConfigTransformer,
        ConfigValidateTransformer $emailConfigTransformer,
        Manager $fractal
    ) {
        $this->middleware('setDealerIdOnRequest')->only(['index', 'salesReport']);

        $this->salesPerson = $salesPersonRepo;
        $this->salesService = $salesPersonService;
        $this->salesPersonTransformer = $salesPersonTransformer;
        $this->salesPersonConfigTransformer = $salesPersonConfigTransformer;
        $this->emailConfigTransformer = $emailConfigTransformer;
        $this->fractal = $fractal;

        $this->fractal->setSerializer(new NoDataArraySerializer());
    }

    public function index(Request $request)
    {
        $request = new GetSalesPeopleRequest($request->all());
        if ($request->validate()) {
            return $this->response->paginator($this->salesPerson->getAll($request->all()), $this->salesPersonTransformer);
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
     * Get Sales Person
     * 
     * @param int $id
     * @return type
     */
    public function show(int $id)
    {
        // Handle Auth Sales People Request
        $requestData = ['sales_person_id' => $id];
        $request = new GetSalesPersonRequest($requestData);
        if ($request->validate()) {
            // Return Auth
            return $this->response->item($this->salesPerson->get($request->all()), $this->salesPersonTransformer);
        }
        
        return $this->response->errorBadRequest();
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
            return $this->response->item($this->salesService->validate($request->all()), $this->emailConfigTransformer);
        }

        return $this->response->errorBadRequest();
    }

    public function config(Request $request): Response
    {
        $request = new ConfigSalesPeopleRequest($request->all());

        if ($request->validate()) {
            // Return Item SalesPersonConfig
            $data = new Item(new SalesPersonConfig(), $this->salesPersonConfigTransformer, 'data');
            $response = $this->fractal->createData($data)->toArray();
            return $this->response->array($response);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Delete Sales Person
     * 
     * @param int $id
     * @return type
     */
    public function destroy(int $id)
    {
        $requestData = ['id' => $id];
        $request = new DeleteSalesPersonRequest($requestData);

        if ($request->validate()) {
           
            $deleted = $this->salesPerson->delete($request->all());

            return $this->response->array([
                'deleted' => $deleted
            ]);
        }

        $this->response->errorBadRequest();
    }
}
