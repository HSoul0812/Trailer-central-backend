<?php

namespace App\Http\Controllers;

use App\Utilities\Fractal\NoDataArraySerializer;
use Dingo\Api\Http\Response;
use Dingo\Api\Routing\Helpers;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

/**
 * Class RestfulControllerV2
 *
 * Alternate base restful controller
 *
 * @package App\Http\Controllers
 */
class RestfulControllerV2 extends Controller
{
    use Helpers;

    /**
     * @return Response
     */
    protected function successResponse(): Response
    {
        return $this->response->array([
            'response' => ['status' => 'success']
        ]);
    }

    /**
     * @param mixed $id
     * @return Response
     */
    protected function updatedResponse($id = null): Response
    {
        $params = [
            'response' => ['status' => 'success']
        ];

        if ($id) {
            $params['response']['data'] = ['id' => $id];
        }

        return $this->response->array($params);
    }

    /**
     * @param mixed $id
     * @return Response
     */
    protected function createdResponse($id = null): Response
    {
        $params = [
            'response' => ['status' => 'success']
        ];

        if ($id) {
            $params['response']['data'] = ['id' => $id];
        }

        return $this->response->created(null, $params);
    }

    /**
     * @param mixed $id
     * @return Response
     */
    protected function acceptedResponse($id = null): Response
    {
        $params = [
            'response' => ['status' => 'success']
        ];

        if ($id) {
            $params['response']['data'] = ['id' => $id];
        }

        return $this->response->accepted(null, $params);
    }

    /**
     * @return Response
     */
    protected function deletedResponse(): Response
    {
        return $this->response->noContent();
    }

    /**
     * @param bool $isExists
     * @return Response
     */
    protected function existsResponse(bool $isExists): Response
    {
        return $this->response->array([
            'response' => [
                'status' => 'success',
                'data' => $isExists
            ]
        ]);
    }

    /**
     * @param  $data
     * @param TransformerAbstract $transformer
     * @param LengthAwarePaginator|null $paginator
     * @return Response
     */
    protected function collectionResponse($data, TransformerAbstract $transformer, ?LengthAwarePaginator $paginator = null): Response
    {
        $fractal = new Manager();
        $fractal->setSerializer(new NoDataArraySerializer());

        $fractal->parseIncludes(request()->query('with', ''));

        $collection = new Collection($data, $transformer);

        if ($paginator) {
            $collection->setPaginator(new IlluminatePaginatorAdapter($paginator));
        }

        $responseData = $fractal->createData($collection)->toArray();

        if ($paginator) {
            $meta = $responseData['meta'];
            unset($responseData['meta']);
        }

        return $this->response->array([
            'data' => $responseData,
            'meta' => $meta ?? [],
        ]);
    }

    /**
     * @param mixed $data
     * @param TransformerAbstract $transformer
     * @return Response
     */
    protected function itemResponse($data, TransformerAbstract $transformer): Response
    {
        $fractal = new Manager();
        $fractal->setSerializer(new NoDataArraySerializer());

        $fractal->parseIncludes(request()->query('include', ''));

        $item = new Item($data, $transformer);

        $responseData = $fractal->createData($item)->toArray();

        return $this->response->array(['data' => $responseData]);
    }

    /**
     * @param string $xml
     * @return \Illuminate\Http\Response
     */
    protected function xmlResponse(string $xml): \Illuminate\Http\Response
    {
        return response($xml, 200, [
            'Content-Type' => 'application/xml'
        ]);
    }
}
