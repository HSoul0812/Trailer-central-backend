<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Pos;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Pos\Sales\Reports\PostCustomSalesReportRequest;
use App\Repositories\Pos\SalesReportRepositoryInterface;
use App\Transformers\Pos\Sales\Reports\CustomSalesReportTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Controller for Sales Reports API
 */
class SalesReportController extends RestfulControllerV2
{
    /**
     * @var SalesReportRepositoryInterface
     */
    private $salesRepository;

    /**
     * @var Manager
     */
    private $fractal;

    public function __construct(SalesReportRepositoryInterface $salesRepository, Manager $fractal)
    {
        $this->salesRepository = $salesRepository;
        $this->middleware('setDealerIdOnRequest')->only(['customReport']);
        $this->fractal = $fractal;

        $this->fractal->setSerializer(new NoDataArraySerializer());
    }

    /**
     * Provides the Custom Sales transactions
     *
     * @param Request $request
     *
     * @return Response|void when there is a bad request it will throw an HttpException and request life cycle ends
     * @OA\Post(
     *     path="/reports/custom-sales"
     * )
     *
     * @throws NoObjectIdValueSetException
     * @throws HttpException when there is a bad request
     */
    public function customReport(Request $request): Response
    {
        $request = new PostCustomSalesReportRequest($request->all());

        if ($request->validate()) {
            $data = new Collection(
                $this->salesRepository->customReport($request->all()),
                new CustomSalesReportTransformer(),
                'data'
            );

            return $this->response->array($this->fractal->createData($data)->toArray());
        }

        $this->response->errorBadRequest();
    }
}
