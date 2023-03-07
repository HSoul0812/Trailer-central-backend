<?php

namespace App\Http\Controllers\v1\Dms\UnitSale;

use App\Domains\UnitSale\Actions\GetUnitSaleRefundsAction;
use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Dms\Quotes\GetQuoteRefundsRequest;
use App\Transformers\Dms\RefundTransformer;
use Carbon\Carbon;
use Dingo\Api\Http\Request;
use Exception;

class UnitSaleRefundsController extends RestfulControllerV2
{
    /** @var int The default per_page value if not provided */
    const PER_PAGE = 10;
    
    public function __construct()
    {
        $this->middleware('setDealerIdOnRequest')->only(['index']);
    }

    /**
     * @throws NoObjectTypeSetException
     * @throws NoObjectIdValueSetException
     * @throws Exception
     */
    public function index(Request $request, GetUnitSaleRefundsAction $getUnitSale, int $unitSaleId)
    {
        $request = new GetQuoteRefundsRequest($request->all());

        $request->validate();

        $paginator = $getUnitSale
            ->withPage($request->input('page'))
            ->withPerPage($request->input('per_page', self::PER_PAGE))
            ->when($request->has('with'), function (GetUnitSaleRefundsAction $action) use ($request) {
                $relations = explode(',', $request->input('with'));

                $action->withRelations($relations);
            })
            ->when($request->has('sort'), function (GetUnitSaleRefundsAction $action) use ($request) {
                $action->withSort($request->input('sort'));
            })
            ->when($request->has('created_at_between'), function (GetUnitSaleRefundsAction $action) use ($request) {
                $segments = explode(',', $request->input('created_at_between'));

                $createdAtBetween = collect($segments)
                    ->map(function (string $dateTime) {
                        return Carbon::parse(trim($dateTime));
                    })
                    ->toArray();

                $action->withCreatedAtBetween($createdAtBetween);
            })
            ->withCustomerId($request->input('customer_id'))
            ->withRegisterId($request->input('register_id'))
            ->execute($unitSaleId);

        return $this->collectionResponse($paginator->items(), new RefundTransformer(), $paginator);
    }
}
