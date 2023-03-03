<?php

namespace App\Http\Controllers\v1\Feed;

use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\RestfulController;
use App\Jobs\Import\Feed\DealerFeedImporterJob;

use App\Http\Requests\Feed\Factory\UploadFactoryFeedUnitRequest;

use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;

/**
 * Class UploadController
 *
 * For APIs for upload of dealer data
 *
 * @package App\Http\Controllers\v1\Uploader
 */
class UploadController extends RestfulController
{
    public function __construct()
    {
        $this->middleware('setDealerIdOnRequest')->only(['upload']);
    }

    /**
     * Upload source data
     *
     * @param Request $request
     * @param string $code
     * @return Response
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function upload(Request $request, string $code): Response
    {
        $request = new UploadFactoryFeedUnitRequest(array_merge($request->all(), ['code' => $code]));

        if (!$request->validate()) {
            return $this->response->errorBadRequest();
        }

        dispatch((new DealerFeedImporterJob($request->all(), $code))->onQueue('factory-feeds'));

        return $this->successResponse();
    }
}
