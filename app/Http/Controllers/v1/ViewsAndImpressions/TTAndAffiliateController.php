<?php

namespace App\Http\Controllers\v1\ViewsAndImpressions;

use App\Domains\ViewsAndImpressions\Actions\GetTTAndAffiliateViewsAndImpressionsAction;
use App\Domains\ViewsAndImpressions\DTOs\GetTTAndAffiliateViewsAndImpressionCriteria;
use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\UpdateRequestInterface;
use App\Http\Requests\ViewsAndImpressions\IndexTTAndAffiliateViewsAndImpressionsRequest;

class TTAndAffiliateController extends AbstractRestfulController
{
    public function index(IndexRequestInterface $request)
    {
        $request->validate();

        $criteria = GetTTAndAffiliateViewsAndImpressionCriteria::fromRequest($request);

        $viewsAndImpressions = resolve(GetTTAndAffiliateViewsAndImpressionsAction::class)
            ->setCriteria($criteria)
            ->execute();

        return $this->response->array(
            array: $viewsAndImpressions,
        );
    }

    public function create(CreateRequestInterface $request)
    {
        throw new NotImplementedException();
    }

    public function show(int $id)
    {
        throw new NotImplementedException();
    }

    public function update(int $id, UpdateRequestInterface $request)
    {
        throw new NotImplementedException();
    }

    public function destroy(int $id)
    {
        throw new NotImplementedException();
    }

    protected function constructRequestBindings(): void
    {
        app()->bind(IndexRequestInterface::class, function () {
            return inject_request_data(IndexTTAndAffiliateViewsAndImpressionsRequest::class);
        });
    }
}
