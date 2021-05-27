<?php


namespace App\Http\Controllers\v1\Dms;


use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\CRM\Invoice\InvoiceRepositoryInterface;
use App\Transformers\Dms\InvoiceTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use Dingo\Api\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use OpenApi\Annotations as OA;

/**
 * Class InvoiceController
 * @package App\Http\Controllers\v1\Dms
 */
class InvoiceController extends RestfulControllerV2
{

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;
    /**
     * @var InvoiceTransformer
     */
    private $invoiceTransformer;
    /**
     * @var Manager
     */
    private $fractal;

    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        InvoiceTransformer $invoiceTransformer,
        Manager $fractal
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceTransformer = $invoiceTransformer;
        $this->fractal = $fractal;

        $this->fractal->setSerializer(new NoDataArraySerializer());
    }

    /**
     * @param $id
     * @param Request $request
     * @param InvoiceRepositoryInterface $repository
     * @return \Dingo\Api\Http\Response
     *
     * @OA\Get(
     *     path="/invoices",
     *     @OA\Parameter(
     *          name="with",
     *          description="model relations to load",
     *          in="query"
     *     ),
     *     @OA\Parameter(
     *          name="filter",
     *          description="filters to apply, like where clauses",
     *          in="query"
     *     ),
     *     @OA\Parameter(
     *          name="sort",
     *          description="sort specs",
     *          in="query"
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of invoices",
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

        $sale = $this->invoiceRepository
            ->withRequest($request) // pass jsonapi request queries onto this queryable repo
            ->get([]);

        $data = new Collection($sale, $this->invoiceTransformer);
        $data->setPaginator(new IlluminatePaginatorAdapter($this->invoiceRepository->getPaginator()));

        return $this->response->array([
            'data' => $this->fractal->createData($data)->toArray()
        ]);

    }

    /**
     * @param $id
     * @param Request $request
     * @param InvoiceRepositoryInterface $repository
     * @return \Dingo\Api\Http\Response
     *
     * @OA\Get(
     *     path="/invoice/{$id}",
     *     @OA\Parameter(
     *          name="id",
     *          in="path"
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a single invoice record",
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

        $invoice = $this->invoiceRepository
            ->withRequest($request) // pass jsonapi request queries onto this queryable repo
            ->find($id);
        $data = new Item($invoice, $this->invoiceTransformer);

        return $this->response->array([
            'data' => $this->fractal->createData($data)->toArray()
        ]);
    }
}
