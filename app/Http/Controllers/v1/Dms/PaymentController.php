<?php


namespace App\Http\Controllers\v1\Dms;


use App\Http\Controllers\RestfulControllerV2;
use App\Models\CRM\Account\Payment;
use App\Utilities\JsonApi\QueryBuilder;
use Dingo\Api\Http\Request;

class PaymentController extends RestfulControllerV2
{
    public function show($id, Request $request, QueryBuilder $builder)
    {
        // instantiate a model query builder
        $eloquent = Payment::query();

        // build the query
        $query = $builder
            ->withRequest($request)
            ->withQuery($eloquent)
            ->build();

        return response()->json(['data' => $query->findOrFail($id)]);
    }
}
