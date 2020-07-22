<?php


namespace App\Http\Controllers\v1\Dms;


use App\Exceptions\GenericClientException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Dms\CreateFinancingCompanyRequest;
use App\Http\Requests\Dms\DeleteFinancingCompanyRequest;
use App\Http\Requests\Dms\UpdateFinancingCompanyRequest;
use App\Repositories\Dms\FinancingCompanyRepositoryInterface;
use App\Transformers\Dms\FinancingCompanyTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use Dingo\Api\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;

class FinancingCompanyController extends RestfulControllerV2
{
    /**
     * @var FinancingCompanyRepositoryInterface
     */
    private $financingCompanyRepository;
    /**
     * @var FinancingCompanyTransformer
     */
    private $financingCompanyTransformer;
    /**
     * @var Manager
     */
    private $fractal;

    public function __construct(
        FinancingCompanyRepositoryInterface $financingCompanyRepository,
        FinancingCompanyTransformer $financingCompanyTransformer,
        Manager $fractal
    ) {
        $this->financingCompanyRepository = $financingCompanyRepository;
        $this->financingCompanyTransformer = $financingCompanyTransformer;
        $this->fractal = $fractal;

        $this->fractal->setSerializer(new NoDataArraySerializer());
        $this->middleware('setDealerIdOnRequest')->only(['index', 'create', 'update']);
    }

    /**
     * @param Request $request
     */
    public function index(Request $request)
    {
        $this->fractal->setSerializer(new ArraySerializer());
        $this->fractal->parseIncludes($request->query('with', ''));

        $result = $this->financingCompanyRepository
            ->withRequest($request) // pass jsonapi request queries onto this queryable repo
            ->get([]);

        $data = new Collection($result, $this->financingCompanyTransformer);
        $data->setPaginator(new IlluminatePaginatorAdapter($this->financingCompanyRepository->getPaginator()));

        $result = (array)$this->fractal->createData($data)->toArray();
        return $this->response->array($result);
    }

    public function show($id, Request $request)
    {
        $this->fractal->parseIncludes($request->query('with', ''));

        $invoice = $this->financingCompanyRepository
            ->withRequest($request) // pass jsonapi request queries onto this queryable repo
            ->find($id);
        $data = new Item($invoice, $this->financingCompanyTransformer);

        return $this->response->array([
            'data' => $this->fractal->createData($data)->toArray()
        ]);
    }

    /**
     *
     */
    public function create(Request $request)
    {
        $request = new CreateFinancingCompanyRequest($request->all());

        try {
            if (!$request->validate()) {
                throw new GenericClientException("Validation failed", 400);
            }

            $financingCompany = $this->financingCompanyRepository->create($request->all());
            if (!$financingCompany) {
                throw new \Exception("Could not save", 400);
            }

            return $this->response->item($financingCompany, $this->financingCompanyTransformer);

        } catch (GenericClientException $e) {
            return $this->response->array([
                'error' => $e->getMessage()
            ])->setStatusCode($e->getCode());

        } catch (\Exception $e) {
            return $this->response->array([
                'error' => $e->getMessage()
            ])->setStatusCode(500);

        }
    }

    /**
     *
     */
    public function update($id, Request $request)
    {
        $params = $request->all();
        $params['id'] = $id;
        $request = new UpdateFinancingCompanyRequest($params);

        try {
            if (!$request->validate()) {
                throw new GenericClientException("Validation failed", 400);
            }

            $financingCompany = $this->financingCompanyRepository->update($request->all());
            if (!$financingCompany) {
                throw new \Exception("Could not save", 400);
            }

            return $this->response->item($financingCompany, $this->financingCompanyTransformer);

        } catch (GenericClientException $e) {
            return $this->response->array([
                'error' => $e->getMessage()
            ])->setStatusCode($e->getCode());

        } catch (\Exception $e) {
            return $this->response->array([
                'error' => $e->getMessage()
            ])->setStatusCode(500);

        }
    }

    public function destroy($id)
    {
        $deleteRequest = new DeleteFinancingCompanyRequest(['id' => $id]);

        try {
            if (!$deleteRequest->validate()) {
                throw new GenericClientException('The record being deleted is not owned', 403);
            }

            if (!$this->financingCompanyRepository->delete(['id' => $deleteRequest->input('id')])) {
                throw new \Exception('Unable to delete', 500);
            }

            return $this->response->array([
                'message' => 'Deleted'
            ]);

        } catch (GenericClientException $e) {
            return $this->response->array([
                'message' => $e->getMessage()
            ])->setStatusCode($e->getCode()? $e->getCode(): 400);

        } catch (\Exception $e) {
            return $this->response->array([
                'message' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }

}
