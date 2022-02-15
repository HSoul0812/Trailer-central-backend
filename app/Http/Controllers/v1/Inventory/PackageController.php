<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Inventory\Packages\CreatePackageRequest;
use App\Http\Requests\Inventory\Packages\DeletePackageRequest;
use App\Http\Requests\Inventory\Packages\GetPackageRequest;
use App\Http\Requests\Inventory\Packages\GetPackagesRequest;
use App\Http\Requests\Inventory\Packages\UpdatePackageRequest;
use App\Repositories\Inventory\Packages\PackageRepositoryInterface;
use App\Services\Inventory\Packages\PackageServiceInterface;
use App\Transformers\Inventory\Packages\PackageTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

/**
 * Class PackageController
 * @package App\Http\Controllers\v1\Inventory
 */
class PackageController extends RestfulControllerV2
{
    /**
     * @var PackageRepositoryInterface
     */
    private $packageRepository;

    /**
     * @var PackageServiceInterface
     */
    private $packageService;

    /**
     * PackageController constructor.
     * @param PackageRepositoryInterface $packageRepository
     * @param PackageServiceInterface $packageService
     */
    public function __construct(PackageRepositoryInterface $packageRepository, PackageServiceInterface $packageService)
    {
        $this->middleware('setDealerIdOnRequest')->only(['index', 'show', 'create', 'update', 'destroy']);

        $this->packageRepository = $packageRepository;
        $this->packageService = $packageService;
    }

    /**
     * @OA\Get(
     *     path="/api/inventory/packages",
     *     description="Retrieve a list of inventory packages",

     *     tags={"Ivnentory Packages"},
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="query",
     *         description="Dealer ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Per Page",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of inventory packages",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request",
     *     ),
     * )
     */
    public function index(Request $request): Response
    {
        $request = new GetPackagesRequest($request->all());

        if ($request->validate()) {
            return $this->response->paginator($this->packageRepository->getAll($request->all()), new PackageTransformer());
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/inventory/packages/{id}",
     *     description="Retrieve an inventory package",

     *     tags={"Inventory Packages"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Package ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="query",
     *         description="Dealer ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns an inventory package",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request",
     *     ),
     * )
     */
    public function show(int $id, Request $request): Response
    {
        $request = new GetPackageRequest(array_merge(['id' => $id], $request->all()));

        if ($request->validate()) {
            return $this->response->item($this->packageRepository->get($request->all()), new PackageTransformer());
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Put(
     *     path="/api/inventory/packages",
     *     description="Create an inventory package",

     *     tags={"Inventory Packages"},
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="query",
     *         description="Dealer ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="visible_with_main_item",
     *         in="query",
     *         description="Visible with main item",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="inventories",
     *         in="query",
     *         description="Inventories",
     *         required=true,
     *         @OA\Schema(type="array")
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Returns an inventory package id",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request",
     *     ),
     * )
     */
    public function create(Request $request): Response
    {
        $request = new CreatePackageRequest($request->all());

        if (!$request->validate() || !($package = $this->packageService->create($request->all()))) {
            return $this->response->errorBadRequest();
        }

        return $this->createdResponse($package->id);
    }

    /**
     * @OA\Post(
     *     path="/api/inventory/packages/{id}",
     *     description="Update an inventory package",

     *     tags={"Inventory Packages"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Package ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="query",
     *         description="Dealer ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="visible_with_main_item",
     *         in="query",
     *         description="Visible with main item",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="inventories",
     *         in="query",
     *         description="Inventories",
     *         required=true,
     *         @OA\Schema(type="array")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns an inventory package id",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request",
     *     ),
     * )
     */
    public function update(int $id, Request $request): Response
    {
        $request = new UpdatePackageRequest(array_merge(['id' => $id], $request->all()));

        if (!$request->validate() || !($package = $this->packageService->update($id, $request->all()))) {
            return $this->response->errorBadRequest();
        }

        return $this->updatedResponse($package->id);
    }

    /**
     * @OA\Delete(
     *     path="/api/inventory/packages/{id}",
     *     description="Delete an inventory package",

     *     tags={"Inventory Packages"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Package ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="query",
     *         description="Dealer ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="204",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request",
     *     ),
     * )
     */
    public function destroy(int $id, Request $request): Response
    {
        $request = new DeletePackageRequest(array_merge(['id' => $id], $request->all()));

        if ($request->validate() && $this->packageService->delete($id)) {
            return $this->deletedResponse();
        }

        return $this->response->errorBadRequest();
    }
}
