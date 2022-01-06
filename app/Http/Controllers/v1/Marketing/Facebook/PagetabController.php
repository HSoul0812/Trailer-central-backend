<?php

namespace App\Http\Controllers\v1\Marketing\Facebook;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\Marketing\Facebook\ShowPagetabRequest;
use App\Http\Requests\Marketing\Facebook\GetPagetabRequest;
use App\Http\Requests\Marketing\Facebook\CreatePagetabRequest;
use App\Http\Requests\Marketing\Facebook\UpdatePagetabRequest;
use App\Http\Requests\Marketing\Facebook\DeletePagetabRequest;
use App\Repositories\Integration\Facebook\PageRepositoryInterface;
use App\Transformers\Integration\Facebook\PageTransformer;
use Dingo\Api\Http\Request;

class PagetabController extends RestfulController
{
    /**
     * @var PageRepositoryInterface
     */
    protected $repository;

    /**
     * @var PageTransformer
     */
    protected $transformer;

    /**
     * Create a new controller instance.
     *
     * @param PageRepositoryInterface $repo
     * @param PageTransformer $transformer
     */
    public function __construct(
        PageRepositoryInterface $repo,
        PageTransformer $transformer
    ) {
        $this->repository = $repo;
        $this->transformer = $transformer;

        $this->middleware('setDealerIdOnRequest')->only(['index']);
    }

    /**
     * Get Facebook Page Integrations With Access Tokens
     * 
     * @param Request $request
     * @return type
     */
    public function index(Request $request)
    {
        // Handle Facebook Page Request
        $request = new GetPagetabRequest($request->all());
        if ($request->validate()) {
            // Get Page Integrations
            return $this->response->paginator($this->repository->getAll($request->all()), $this->transformer);
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Get Facebook Page and Access Token
     * 
     * @param Request $request
     * @return type
     */
    public function show(int $id, Request $request)
    {
        // Handle Facebook Page Request
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new ShowPagetabRequest($requestData);
        if ($request->validate()) {
            // Return Auth
            return $this->response->array($this->repository->show($request->all()));
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Create Facebook Page and Access Token
     * 
     * @param Request $request
     * @return type
     */
    public function create(Request $request)
    {
        // Handle Facebook Page Request
        $request = new CreatePagetabRequest($request->all());
        if ($request->validate()) {
            // Return Auth
            return $this->response->array($this->repository->get($request->all()));
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Update Facebook Page and Access Token
     * 
     * @param Request $request
     * @param null|int $id
     * @return type
     */
    public function update(Request $request, ?int $id = null)
    {
        // Handle Facebook Page Request
        $requestData = $request->all();
        if(!empty($id)) {
            $requestData['id'] = $id;
        }
        $request = new UpdatePagetabRequest($requestData);
        if ($request->validate()) {
            // Return Auth
            return $this->response->array($this->repository->update($request->all()));
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Delete Facebook Page and Access Token
     * 
     * @param int $id
     * @return type
     */
    public function destroy(int $id)
    {
        // Handle Facebook Page Request
        $request = new DeletePagetabRequest(['id' => $id]);
        if ($request->validate() && $this->repository->delete($id)) {
            return $this->successResponse();
        }
        
        return $this->response->errorBadRequest();
    }
}