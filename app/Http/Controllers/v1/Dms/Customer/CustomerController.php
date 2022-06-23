<?php

namespace App\Http\Controllers\v1\Dms\Customer;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Dms\CreateCustomerRequest;
use App\Http\Requests\Dms\DeleteCustomerRequest;
use App\Http\Requests\Dms\GetCustomersRequest;
use App\Http\Requests\Dms\UpdateCustomerRequest;
use App\Repositories\CRM\Customer\CustomerRepositoryInterface;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Transformers\Dms\Customer\CustomerDetailTransformer;
use App\Transformers\Dms\CustomerTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Dingo\Api\Exception\ResourceException;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use Dingo\Api\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;

class CustomerController extends RestfulControllerV2
{
    /**
     * @var LeadRepositoryInterface
     */
    protected $leads;

    /**
     * @var CustomerTransformer
     */
    protected $transformer;

    /**
     * @var CustomerDetailTransformer
     */
    protected $detailTransformer;

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
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        LeadRepositoryInterface $leadRepo,
        CustomerTransformer $transformer,
        CustomerDetailTransformer $detailTransformer,
        Manager $fractal
    ) {
        $this->middleware('setDealerIdOnRequest')->only(['index', 'search', 'create', 'update','destroy']);
        $this->leads = $leadRepo;
        $this->fractal = $fractal;
        $this->transformer = $transformer;
        $this->detailTransformer = $detailTransformer;
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
            
            $message = $e->getMessage();
            
            if ($e instanceof ResourceException) {
                $message = collect(Arr::flatten($e->getErrors()->messages()))->implode(', ');
            }

            throw new StoreResourceFailedException("Unable to create customer: $message");
        }
    }

    public function show(int $id) {
        $customer = $this->customerRepository->get(['id' => $id]);

        $user = Auth::user();

        $response = $this->response
                ->item($customer, $this->detailTransformer)
                ->addMeta('major_units_link', config('app.new_design_crm_url') . $user->getCrmLoginUrl('/bill-of-sale'))
                ->addMeta('service_link', config('app.new_design_crm_url') . $user->getCrmLoginUrl('/repair-orders'))
                ->addMeta('parts_link', config('app.new_design_crm_url') . $user->getCrmLoginUrl('/pos-reports'));

        if ($customer->lead) {
            $response = $response->addMeta('see_more_interactions', config('app.url') . "/api/leads/{$customer->lead->identifier}/interactions");
        }

        return $response;
    }

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
            $request = new UpdateCustomerRequest($customerData);
            
            if ($request->validate()) {
                $customer = $this->customerRepository->update($customerData);

                return $this->response->item($customer, $this->transformer);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());

            $message = $e->getMessage();
            
            if ($e instanceof ResourceException) {
                $message = collect(Arr::flatten($e->getErrors()->messages()))->implode(', ');
            }

            throw new UpdateResourceFailedException("Unable to update customer: $message");
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

    /**
     * @OA\Delete(
     *     path="/api/user/customers/{id}",
     *     description="Delete a customer",
     *     tags={"Customers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Customer Id",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="204",
     *         description="Confirms customer was deleted",
     *         @OA\JsonContent()
     *     ),
     * )
     */
    public function destroy(int $id, Request $request) {
        $customerData = new DeleteCustomerRequest(array_merge($request->all(), ['id' => $id]));

        try {
            if ($customerData->validate() && $this->customerRepository->delete($customerData->all())) {
                return $this->response->noContent();
            }
        } catch (ResourceException $e) {
            throw new DeleteResourceFailedException($e->getMessage(), $e->getErrors());
        }

        return $this->response->errorBadRequest();
    }

}
