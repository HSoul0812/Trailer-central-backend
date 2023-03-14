<?php

namespace App\Http\Controllers\v1\User;

use App\Http\Controllers\RestfulControllerV2;
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
     * Store a newly created resource in storage.
     *
     * @param UpdateDealerLogoRequest $request
     * @return Response
     */
    public function store(UpdateDealerLogoRequest $request): Response
    {
        $dealerId = auth()->id();
        $data = [
            'benefit_statement' => $request->benefit_statement
        ];

        if ($request->exists('logo') && !$request->hasFile('logo')) {
            $this->dealerLogoService->delete($dealerId);
            $data['filename'] = null;
        } else if ($logo = $request->file('logo')) {
            $data['filename'] = $this->dealerLogoService->upload($dealerId, $logo);
        }
        return $this->response->item($this->dealerLogoRepository->update($dealerId, $data), $this->dealerLogoTransformer);
    }
}
