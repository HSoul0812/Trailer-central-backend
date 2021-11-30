<?php
namespace App\Http\Controllers\v1\Ecommerce;

use App\Http\Controllers\RestfulController;
use App\Repositories\Ecommerce\InvoiceRepositoryInterface;
use App\Transformers\Ecommerce\InvoiceTransformer;
use App\Http\Controllers\RestfulControllerV2;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

class InvoiceController extends RestfulControllerV2
{
  /**
   * @param InvoiceRepositoryInterface $invoiceRepo
   */
  public function __construct(InvoiceRepositoryInterface $invoiceRepo, InvoiceTransformer $transformer)
  {
      $this->invoiceRepo = $invoiceRepo;
      $this->transformer = $transformer;
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
      return $this->response->item($this->invoiceRepo->get(['id' => $id]), $this->transformer);
  }

}