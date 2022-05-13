<?php


namespace App\Http\Controllers\v1\Pos;


use App\Domains\ElasticSearch\Actions\EscapeElasticSearchReservedCharactersAction;
use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\Parts\PartRepositoryInterface;
use App\Services\Pos\PosService;
use App\Transformers\Inventory\InventoryTransformerV2;
use App\Transformers\Parts\PartsTransformer;
use App\Transformers\Pos\PosProductTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Log;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;

class PosController extends RestfulControllerV2
{

    /**
     * @var Manager
     */
    private $fractal;

    public function __construct(Manager $fractal)
    {
        $this->middleware('setDealerIdOnRequest')->only(['create', 'search', 'update']);
        $this->fractal = $fractal;
    }

    /**
     * POS products search - includes parts and non serialized inventory
     * @param  Request  $request
     * @param  PartRepositoryInterface  $parts
     * @param  PartsTransformer  $partsTransformer
     * @return \Dingo\Api\Http\Response|void
     */
    public function search(Request $request, PosService $posService, PosProductTransformer $posProductTransformer, PartsTransformer $partsTransformer, InventoryTransformerV2 $inventoryTransformer)
    {
        $actionStart = microtime(true);

        try {
            $this->fractal->setSerializer(new NoDataArraySerializer());
            $this->fractal->parseIncludes($request->query('with', ['images']));

            // We want to make sure that the query string is escaped
            // If we don't do this we will get error when we try to search
            // with special characters like '/', '(', etc.
            $escapedQuery = resolve(EscapeElasticSearchReservedCharactersAction::class)->execute($request->get('query', '') ?? '');
            $request->merge(['query' => $escapedQuery]);

            $query = $request->get('query');

            /** @var \Illuminate\Database\Eloquent\Collection $result */
            $esStart = microtime(true);
            $result = $posService->productSearch($query, $request->input('dealer_id'), ['allowAll' => true]);
            $esEnd = microtime(true) - $esStart;

            $data = new Collection($result, $posProductTransformer);

            $actionEnd = microtime(true) - $actionStart;
            return $this->response->array([
                'data' => $this->fractal->createData($data)->toArray(),
                'meta' => [
                    'es-time' => $esEnd,
                    'action-time' => $actionEnd,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());

            return $this->response->errorBadRequest();
        }

    }
}
