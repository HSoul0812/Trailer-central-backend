<?php

namespace App\Http\Controllers\v1\Dms\Customer;

use App\Http\Controllers\RestfulController;
use App\Repositories\CRM\Customer\CustomerRepositoryInterface;
use App\Utilities\Fractal\NoDataArraySerializer;
use Dingo\Api\Http\Request;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Http\Requests\Dms\GetCustomersRequest;
use App\Transformers\Dms\CustomerTransformer;
use Illuminate\Support\Facades\Log;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;

class CustomerController extends RestfulController
{

    protected $leads;

    protected $transformer;
    /**
     * @var Manager
     */
    private $fractal;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * Create a new controller instance.
     *
     * @param  CustomerRepositoryInterface  $customerRepository
     * @param  LeadRepositoryInterface  $leadRepo
     * @param  CustomerTransformer  $transformer
     * @param  Manager  $fractal
     */
    public function __construct(CustomerRepositoryInterface $customerRepository, LeadRepositoryInterface $leadRepo, CustomerTransformer $transformer, Manager $fractal)
    {
        $this->middleware('setDealerIdOnRequest')->only(['index', 'search']);
        $this->leads = $leadRepo;
        $this->transformer = new CustomerTransformer;
        $this->fractal = $fractal;
        $this->transformer = $transformer;
        $this->customerRepository = $customerRepository;
    }

    public function index(Request $request)
    {
        $request = new GetCustomersRequest($request->all());

        if ($request->validate()) {
            /**
             * Need to migrate lead customers to dms_customer and pull from there
             */
            return $this->response->paginator($this->leads->getCustomers($request->all()), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }

    public function search(Request $request)
    {
        try {
            $this->fractal->setSerializer(new NoDataArraySerializer());
            $this->fractal->parseIncludes($request->query('with', ''));
            $query = $request->only('query', 'sort');
            $paginator = new \stdClass(); // this will hold the paginator produced by search
            $dealerId = $request->input('dealer_id');

            // do the search
            $result = $this->customerRepository->search(
                $query, $dealerId, [
                    'allowAll' => true,
                    'page' => $request->get('page'),
                    'per_page' => $request->get('per_page', 10),
                ], $paginator
            );
            $data = new Collection($result, $this->transformer, 'data');

            // if a paginator is requested
            if ($request->get('page')) {
                $data->setPaginator(new IlluminatePaginatorAdapter($paginator));
            }

            // build the api response
            $result = (array) $this->fractal->createData($data)->toArray();
            return $this->response->array($result);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());

            return $this->response->errorBadRequest($e->getMessage());
        }
    }

}
