<?php


namespace App\Http\Controllers\v1\Dms\ServiceOrder;


use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\Dms\ServiceOrder\ServiceItemTechnicianRepositoryInterface;
use App\Repositories\Dms\ServiceOrder\ServiceItemTechnicianRepository;
use App\Repositories\Dms\ServiceOrderRepositoryInterface;
use App\Transformers\Dms\ServiceOrder\ServiceItemTechnicianReportTransformer;
use App\Transformers\Dms\ServiceOrder\ServiceItemTechnicianTransformer;
use App\Transformers\Dms\ServiceOrderTransformer;
use App\Transformers\Reports\SalesPerson\SalesReportTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use Dingo\Api\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class ServiceItemTechnicianController extends RestfulControllerV2
{
    /**
     * @var ServiceItemTechnicianRepository
     */
    private $serviceItemTechnicians;
    /**
     * @var ServiceItemTechnicianTransformer
     */
    private $transformer;
    /**
     * @var Manager
     */
    private $fractal;

    public function __construct(
        ServiceItemTechnicianRepositoryInterface $serviceItemTechnicians,
        ServiceItemTechnicianTransformer $transformer,
        Manager $fractal
    ) {
        $this->serviceItemTechnicians = $serviceItemTechnicians;
        $this->transformer = $transformer;
        $this->fractal = $fractal;

        $this->middleware('setDealerIdOnRequest')->only(['index', 'byDealer', 'serviceReport']);
        $this->fractal->setSerializer(new NoDataArraySerializer());
    }

    public function index(Request $request)
    {
        $this->fractal->parseIncludes($request->query('with', ''));
        $technicians = $this->serviceItemTechnicians
            ->withRequest($request)
            ->get([]);

        $data = new Collection($technicians, $this->transformer);
        $data->setPaginator(new IlluminatePaginatorAdapter($this->serviceItemTechnicians->getPaginator()));

        return $this->response->array($this->fractal->createData($data)->toArray());
    }

    public function byLocation($locationId, Request $request)
    {
        $this->fractal->parseIncludes($request->query('with', ''));
        $technicians = $this->serviceItemTechnicians
            ->withRequest($request)
            ->findByLocation($locationId);

        $data = new Collection($technicians, $this->transformer, 'data');
        $data->setPaginator(new IlluminatePaginatorAdapter($this->serviceItemTechnicians->getPaginator()));

        return $this->response->array($this->fractal->createData($data)->toArray());
    }

    public function byDealer(Request $request)
    {
        $this->fractal->parseIncludes($request->query('with', ''));
        $technicians = $this->serviceItemTechnicians
            ->withRequest($request)
            ->findByDealer($request->all('dealer_id'));

        $data = new Collection($technicians, $this->transformer, 'data');

        $data->setPaginator(new IlluminatePaginatorAdapter($this->serviceItemTechnicians->getPaginator()));

        return $this->response->array($this->fractal->createData($data)->toArray());
    }

    public function serviceReport(Request $request)
    {
        $result = $this->serviceItemTechnicians->serviceReport($request->all());
        $data = new Item($result, new ServiceItemTechnicianReportTransformer(), 'data');

        $response = $this->fractal->createData($data)->toArray();
        return $this->response->array($response);
    }
}
