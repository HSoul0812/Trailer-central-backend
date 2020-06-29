<?php


namespace App\Http\Controllers\v1\Dms;


use App\Http\Controllers\RestfulControllerV2;
use App\Models\CRM\Account\Invoice;
use App\Utilities\JsonApi\JsonApiRequest;
use App\Utilities\JsonApi\QueryBuilder;
use Dingo\Api\Http\Request;

/**
 * Class InvoiceController
 * @package App\Http\Controllers\v1\Dms
 */
class InvoiceController extends RestfulControllerV2
{
    public function show($id, Request $request, QueryBuilder $builder)
    {
        // instantiate a model query builder
        $eloquent = Invoice::query();

        // build the query
        $query = $builder
            ->withRequest($request)
            ->withQuery($eloquent)
            ->build();

        return response()->json(['data' => $query->findOrFail($id)]);
    }
}
