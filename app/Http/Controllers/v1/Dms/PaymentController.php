<?php


namespace App\Http\Controllers\v1\Dms;


use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\CRM\Payment\PaymentRepositoryInterface;
use App\Transformers\Dms\PaymentTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use Dingo\Api\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use OpenApi\Annotations as OA;

/**
 * Class PaymentController
 *
 * Controller for qb_payment objects
 *
 * @package App\Http\Controllers\v1\Dms
 */
class PaymentController extends RestfulControllerV2
{
    /**
     * @var PaymentRepositoryInterface
     */
    private $paymentRepository;
    /**
     * @var PaymentTransformer
     */
    private $paymentTransformer;
    /**
     * @var Manager
     */
    private $fractal;

    public function __construct(
        PaymentRepositoryInterface $paymentRepository,
        PaymentTransformer $paymentTransformer,
        Manager $fractal
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->paymentTransformer = $paymentTransformer;
        $this->fractal = $fractal;

        $this->fractal->setSerializer(new NoDataArraySerializer());
    }

    /**
     * Return a single payment object
     *
     * @param $id
     * @param Request $request
     * @param PaymentRepositoryInterface $repository
     * @return \Dingo\Api\Http\Response
     *
     * @OA\Get(
     *     path="/payment/{$id}",
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

        $payment = $this->paymentRepository
            ->withRequest($request) // pass jsonapi request queries onto this queryable repo
            ->find($id);
        $data = new Item($payment, $this->paymentTransformer);

        return $this->response->array([
            'data' => $this->fractal->createData($data)->toArray()
        ]);
    }
}
