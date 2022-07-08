<?php

namespace App\Http\Controllers\v1\Dms;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\Dms\GetQuotesRequest;
use App\Http\Requests\Dms\Quotes\GetQuoteRefundsRequest;
use App\Http\Requests\Dms\UnitSale\BulkArchiveUpdateRequest;
use App\Models\CRM\Dms\Refund;
use App\Models\CRM\Dms\UnitSale;
use App\Repositories\CRM\Refund\RefundRepositoryInterface;
use App\Repositories\Dms\QuoteRepositoryInterface;
use App\Services\Dms\UnitSale\UnitSaleServiceInterface;
use App\Transformers\Dms\QuoteTotalsTransformer;
use App\Transformers\Dms\QuoteTransformer;
use Dingo\Api\Http\Request;
use Illuminate\Database\Eloquent\Builder;

/**
 * @author Marcel
 */
class UnitSaleController extends RestfulController
{
    protected $quotes;

    /**
     * @var UnitSaleServiceInterface $service
     */
    private $service;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        QuoteRepositoryInterface $quotes,
        UnitSaleServiceInterface $unitSaleService
    ) {
        $this->middleware('setDealerIdOnRequest')->only(['index', 'bulkArchive', 'refunds']);
        $this->quotes = $quotes;
        $this->service = $unitSaleService;
    }

    /**
     * @OA\Get(
     *     path="/api/quotes",
     *     description="Retrieve a list of quotes",
     *     tags={"Quote"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Page Limit",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="query",
     *         description="Dealer ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Status of quote",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="search_term",
     *         in="query",
     *         description="Search term",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="include_group_data",
     *         in="query",
     *         description="Flag whether group info is included. If not provided, it pass group info by default.",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of quotes",
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
        $request = new GetQuotesRequest($request->all());

        if ($request->validate()) {
            if ($request->input('include_group_data') !== null && empty($request->input('include_group_data'))) {
                return $this->response->paginator($this->quotes->getAll($request->all()), new QuoteTransformer);
            } else {
                $groupData = (new QuoteTotalsTransformer)->transform($this->quotes->getTotals($request->all()));

                return $this->response
                    ->paginator($this->quotes->getAll($request->all()), new QuoteTransformer)
                    ->addMeta('totals', $groupData);
            }
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Put(
     *     path="/api/quotes/bulk-archive",
     *     description="Archive multiple quotes",
     *     tags={"Quote"},
     *     @OA\Parameter(
     *         name="quote_ids",
     *         in="query",
     *         description="Quote Ids",
     *         required=true,
     *         @OA\Schema(type="array")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns 200 HTTP status if succeed in updating single record",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function bulkArchive(BulkArchiveUpdateRequest $request)
    {
        if ($this->service->bulkArchive(
            $request->only('quote_ids'),
            $request->getDealerId()
        )) {
            return $this->response->array(['message' => 'success']);
        }

        return $this->response->errorBadRequest();
    }

    public function refunds(Request $request, int $quoteId)
    {
        $request = new GetQuoteRefundsRequest($request->all());
        
        return Refund::query()
            ->when($request->has('with'), function (Builder $builder) use ($request, $quoteId) {
                $relations = explode(',', $request->get('with'));
            
                $builder->with($relations);
            })
            ->where('dealer_id', $request->get('dealer_id'))
            ->where(function (Builder $builder) use ($request, $quoteId) {
                $builder
                    ->where(function (Builder $builder) use ($request, $quoteId) {
                        $builder
                            ->where('tb_name', UnitSale::getTableName())
                            ->where('tb_primary_id', $quoteId);
                    })
                    ->orWhereHas('invoice', function(Builder $builder) use ($request, $quoteId) {
                        $builder->where('unit_sale_id', $quoteId);
                    });
            })
            ->when($request->has('sort'), function (Builder $builder) use ($request) {
                $column = $request->get('sort');
                $direction = $column[0] === '-' ? 'desc' : 'asc';
                $column = str_replace('-', '', $column);

                $builder->orderBy($column, $direction);
            })
            ->paginate($request->get('per_page'));
    }
}
