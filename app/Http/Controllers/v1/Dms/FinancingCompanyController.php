<?php


namespace App\Http\Controllers\v1\Dms;


use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Dms\CreateFinancingCompanyRequest;
use App\Http\Requests\Dms\DeleteFinancingCompanyRequest;
use App\Http\Requests\Dms\UpdateFinancingCompanyRequest;
use App\Models\CRM\Dms\FinancingCompany;
use App\Repositories\Dms\FinancingCompanyRepositoryInterface;
use App\Transformers\Dms\FinancingCompanyTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use Dingo\Api\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use OpenApi\Annotations as OA;

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

        // todo scopes can be used to assign dealer_id to query by default
        $baseQuery = FinancingCompany::where('dealer_id', $request->input('dealer_id'));
        $result = $this->financingCompanyRepository
            ->withRequest($request) // pass jsonapi request queries onto this queryable repo
            ->withQuery($baseQuery) // query to base this on
            ->get([]);

        $data = new Collection($result, $this->financingCompanyTransformer);
        $data->setPaginator(new IlluminatePaginatorAdapter($this->financingCompanyRepository->getPaginator()));

        $result = (array)$this->fractal->createData($data)->toArray();
        return $this->response->array($result);
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     *
     * @OA\Get(
     *     path="/api/dms/financing-companies/{id}",
     *     description="Retrieve a single financing ocmpany object",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Primary key",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
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
     * @OA\Post(
     *     path="/api/dms/financing-companies",
     *     description="create a financing company object",
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function create(Request $request)
    {
        $request = new CreateFinancingCompanyRequest($request->all());

        if ($request->validate() && $financingCompany = $this->financingCompanyRepository->create($request->all())) {
            return $this->response->item($financingCompany, $this->financingCompanyTransformer);
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     *
     * @OA\Put(
     *     path="/api/dms/financing-companies/{id}",
     *     description="create a financing company object",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Primary key",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function update($id, Request $request)
    {
        $params = $request->all();
        $params['id'] = $id;
        $request = new UpdateFinancingCompanyRequest($params);

        if ($request->validate() && $financingCompany = $this->financingCompanyRepository->update($request->all())) {
            return $this->response->item($financingCompany, $this->financingCompanyTransformer);
        }

        return $this->response->errorBadRequest();
    }

    /**
     *
     * @OA\Delete(
     *     path="/api/dms/financing-companies/{id}",
     *     description="create a financing company object",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Primary key",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */    
    public function destroy($id)
    {
        $deleteRequest = new DeleteFinancingCompanyRequest(['id' => $id]);
        if ($deleteRequest->validate() && $this->financingCompanyRepository->delete(['id' => $id])) {
            return $this->response->noContent();
        }

        return $this->response->errorBadRequest();
    }

}
