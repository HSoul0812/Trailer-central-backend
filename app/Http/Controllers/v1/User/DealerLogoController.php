<?php

namespace App\Http\Controllers\v1\User;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\User\CreateDealerLogoRequest;
use App\Http\Requests\User\UpdateDealerLogoRequest;
use App\Repositories\User\DealerLogoRepositoryInterface;
use App\Services\User\DealerLogoServiceInterface;
use App\Transformers\User\DealerLogoTransformer;
use Illuminate\Http\Response;

class DealerLogoController extends RestfulControllerV2
{
    /**
     * @var DealerLogoRepositoryInterface
     */
    private $dealerLogoRepository;

    /**
     * @var DealerLogoTransformer
     */
    private $dealerLogoTransformer;

    /**
     * @var DealerLogoServiceInterface
     */
    private $dealerLogoService;

    /**
     * @param DealerLogoRepositoryInterface $dealerLogoRepository
     * @param DealerLogoServiceInterface $dealerLogoService
     * @param DealerLogoTransformer $dealerLogoTransformer
     */
    public function __construct(DealerLogoRepositoryInterface $dealerLogoRepository, DealerLogoServiceInterface $dealerLogoService, DealerLogoTransformer $dealerLogoTransformer)
    {
        $this->dealerLogoRepository = $dealerLogoRepository;
        $this->dealerLogoTransformer = $dealerLogoTransformer;
        $this->dealerLogoService = $dealerLogoService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->response->item(
            $this->dealerLogoRepository->get(auth()->id()),
            $this->dealerLogoTransformer
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateDealerLogoRequest $request
     * @return Response
     */
    public function store(CreateDealerLogoRequest $request): Response
    {
        $dealerId = auth()->id();
        $filename = $this->dealerLogoService->upload($dealerId, $request->file('logo'));

        return $this->response->item($this->dealerLogoRepository->create([
            'dealer_id' => $dealerId,
            'filename' => $filename,
            'benefit_statement' => $request->benefit_statement
        ]), $this->dealerLogoTransformer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateDealerLogoRequest $request
     * @return Response
     */
    public function update(UpdateDealerLogoRequest $request): Response
    {
        $dealerId = auth()->id();
        $data = [
            'benefit_statement' => $request->benefit_statement
        ];

        if ($request->hasFile('logo')) {
            $data['filename'] = $this->dealerLogoService->upload($dealerId, $request->file('logo'));
        }

        $this->dealerLogoRepository->update($dealerId, $data);
        return $this->updatedResponse();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(): Response
    {
        $this->dealerLogoRepository->delete(auth()->id());
        return $this->deletedResponse();
    }
}
