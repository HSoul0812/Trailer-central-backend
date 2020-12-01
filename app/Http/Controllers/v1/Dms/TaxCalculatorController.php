<?php


namespace App\Http\Controllers\v1\Dms;


use App\Http\Controllers\RestfulControllerV2;
use App\Models\CRM\Dms\TaxCalculator;
use App\Repositories\Dms\TaxCalculatorRepositoryInterface;
use App\Transformers\Dms\TaxCalculatorTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use Dingo\Api\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\ArraySerializer;

class TaxCalculatorController extends RestfulControllerV2
{
    /**
     * @var TaxCalculatorRepositoryInterface
     */
    private $taxCalculators;
    /**
     * @var TaxCalculatorTransformer
     */
    private $transformer;
    /**
     * @var Manager
     */
    private $fractal;

    public function __construct(
        TaxCalculatorRepositoryInterface $taxCalculatorRepository,
        TaxCalculatorTransformer $taxCalculatorTransformer,
        Manager $fractal
    ) {
        $this->taxCalculators = $taxCalculatorRepository;
        $this->transformer = $taxCalculatorTransformer;
        $this->fractal = $fractal;

        $this->fractal->setSerializer(new NoDataArraySerializer());
        $this->middleware('setDealerIdOnRequest')->only(['index']);
    }

    public function index(Request $request)
    {
        $this->fractal->setSerializer(new ArraySerializer());
        $this->fractal->parseIncludes($request->query('with', ''));

        // todo scopes can be used to assign dealer_id to query by default
        $result = $this->taxCalculators
            ->withRequest($request) // pass jsonapi request queries onto this queryable repo
            ->withQuery(TaxCalculator::where('dealer_id', $request->input('dealer_id')))
            ->get([]);

        $data = new Collection($result, $this->transformer);
        $data->setPaginator(new IlluminatePaginatorAdapter($this->taxCalculators->getPaginator()));

        $result = (array)$this->fractal->createData($data)->toArray();
        return $this->response->array($result);
    }
}
