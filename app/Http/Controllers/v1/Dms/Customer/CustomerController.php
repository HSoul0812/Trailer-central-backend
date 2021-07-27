<?php

namespace App\Http\Controllers\v1\Dms\Customer;

use App\Http\Controllers\RestfulController;
use App\Repositories\CRM\Customer\CustomerRepositoryInterface;
use App\Utilities\Fractal\NoDataArraySerializer;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use Dingo\Api\Http\Request;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Http\Requests\Dms\GetCustomersRequest;
use App\Http\Requests\Dms\CreateCustomerRequest;
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
        $this->middleware('setDealerIdOnRequest')->only(['index', 'search', 'create', 'update']);
        $this->leads = $leadRepo;
        $this->transformer = new CustomerTransformer;
        $this->fractal = $fractal;
        $this->transformer = $transformer;
        $this->customerRepository = $customerRepository;
    }

    public function create(Request $request)
    {
        $customerData = $request->only([
            'dealer_id', 'first_name', 'last_name', 'display_name', 'email', 'drivers_license', 'home_phone',
            'work_phone', 'cell_phone', 'address', 'city', 'region', 'postal_code', 'country', 'website_lead_id',
            'tax_exempt', 'is_financing_company', 'account_number', 'qb_id', 'gender', 'dob', 'deleted_at',
            'is_wholesale', 'default_discount_percent', 'middle_name', 'company_name', 'use_same_address',
            'shipping_address', 'shipping_city', 'shipping_region', 'shipping_postal_code', 'shipping_country',
            'county', 'shipping_county'
        ]);

        try {
            $request = new CreateCustomerRequest($customerData);
            if ($request->validate()) {
                $customer = $this->customerRepository->create($customerData);

                return $this->response->item($customer, $this->transformer);
            }

            return $this->response->errorBadRequest();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());

            throw new StoreResourceFailedException('Unable to create customer: ' . $e->getMessage());
        }
    }
    
//    public function show(int $id) {
//        $this->response->item($this->customerRepository->get(['id' => $id]), $this->transformer);
//    }

    public function update($id, Request $request)
    {
        $customerData = $request->only([
            'dealer_id', 'first_name', 'last_name', 'display_name', 'email', 'drivers_license', 'home_phone',
            'work_phone', 'cell_phone', 'address', 'city', 'region', 'postal_code', 'country', 'website_lead_id',
            'tax_exempt', 'is_financing_company', 'account_number', 'qb_id', 'gender', 'dob', 'deleted_at',
            'is_wholesale', 'default_discount_percent', 'middle_name', 'company_name', 'use_same_address',
            'shipping_address', 'shipping_city', 'shipping_region', 'shipping_postal_code', 'shipping_country',
            'county', 'shipping_county',
        ]);

        $customerData['id'] = $id;

        try {
            $customer = $this->customerRepository->update($customerData);

            return $this->response->item($customer, $this->transformer);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());

            throw new UpdateResourceFailedException('Unable to update customer: ' . $e->getMessage());
        }
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
