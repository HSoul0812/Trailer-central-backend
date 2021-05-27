<?php


namespace App\Http\Controllers\v1\Pos;


use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\Pos\SaleRepositoryInterface;
use App\Transformers\Pos\SaleTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use Dingo\Api\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use OpenApi\Annotations as OA;

/**
 * Class SalesController
 *
 * Controller for POS sales API
 *
 * @package App\Http\Controllers\v1\Pos
 */
class SalesController extends RestfulControllerV2
{
    /**
     * @var SaleRepositoryInterface
     */
    private $saleRepository;
    /**
     * @var SaleTransformer
     */
    private $saleTransformer;
    /**
     * @var Manager
     */
    private $fractal;

    public function __construct(
        SaleRepositoryInterface $saleRepository,
        SaleTransformer $saleTransformer,
        Manager $fractal
    ) {
        $this->saleRepository = $saleRepository;
        $this->saleTransformer = $saleTransformer;
        $this->fractal = $fractal;

        $this->fractal->setSerializer(new NoDataArraySerializer());
    }

    /**
     * List/browse all pos-based queries
     *
     * @param Request $request
     *
     * @return \Dingo\Api\Http\Response
     * @OA\Get(
     *     path="/pos/sales",
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
    public function index(Request $request)
    {
        $this->fractal->parseIncludes($request->query('with', ''));

        $sale = $this->saleRepository
            ->withRequest($request) // pass jsonapi request queries onto this queryable repo
            ->get([]);

        $data = new Collection($sale, $this->saleTransformer);
        $data->setPaginator(new IlluminatePaginatorAdapter($this->saleRepository->getPaginator()));

        return $this->response->array([
            'data' => $this->fractal->createData($data)->toArray()
        ]);
    }

    /**
     * Return a single POS sale record
     * @param $id
     *
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     *
     * @OA\Get(
     *     path="/pos/sale/{id}",
     *     description="Get a POS sales record",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="POS Sales ID",
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

        $sale = $this->saleRepository
            ->withRequest($request) // pass jsonapi request queries onto this queryable repo
            ->find($id);
        $data = new Item($sale, $this->saleTransformer);

        return $this->response->array([
            'data' => $this->fractal->createData($data)->toArray()
        ]);
    }


}
