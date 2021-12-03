<?php
namespace App\Http\Controllers\v1\Ecommerce;

use App\Http\Controllers\RestfulController;
use App\Transformers\Ecommerce\InvoiceTransformer;
use App\Http\Requests\Ecommerce\GetSingleCompletedOrderRequest;
use App\Services\Ecommerce\CompletedOrder\CompletedOrderServiceInterface;
use App\Http\Controllers\RestfulControllerV2;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

class InvoiceController extends RestfulControllerV2
{
  /** @var CompletedOrderServiceInterface */
  private $completedOrderService;
  
  /**
   * InvoiceController constructor.
   * @param CompletedOrderServiceInterface $completedOrderService
   */
  public function __construct(InvoiceTransformer $transformer, CompletedOrderServiceInterface $completedOrderService)
  {
      $this->middleware('setDealerIdOnRequest')->only(['show']);
      $this->transformer = $transformer;
      $this->completedOrderService = $completedOrderService;
  }

  /**
   * @param int $id
   * @param Request $request
   * @return Response
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException when there was a bad request
   *
   */
  public function show(int $id, Request $request): Response
  {
      $orderRequest = new GetSingleCompletedOrderRequest($request->all() + ['order_id' => $id]);
      
      if ($orderRequest->validate()) {
          return $this->response->item($this->completedOrderService->getInvoice(['id' => $id]), $this->transformer);
      }

      $this->response->errorBadRequest();
  }

}