<?php

namespace App\Http\Controllers\v1\CRM\Email;

use App\Http\Controllers\RestfulControllerV2;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use App\Services\CRM\Email\MosaicoServiceInterface;
use App\Http\Requests\CRM\Email\Mosaico\UploadImagesRequest;
use App\Http\Requests\CRM\Email\Mosaico\GetImagesRequest;
use App\Http\Requests\CRM\Email\Mosaico\ProcessImageRequest;
use App\Http\Requests\CRM\Email\Mosaico\ProcessHtmlRequest;

class MosaicoController extends RestfulControllerV2
{
    /**
     * @var MosaicoServiceInterface
     */
    protected $service;

    /**
     * Create a new controller instance.
     *
     * @param MosaicoServiceInterface $service
     */
    public function __construct(MosaicoServiceInterface $service)
    {
        $this->service = $service;

        $this->middleware('setDealerIdOnRequest')->only(['uploadImages', 'getImages', 
            'processImage', 'processHtml', 'getConfigs']);
        $this->middleware('setUserIdOnRequest')->only(['processHtml']);
    }

    public function processImage(Request $request)
    {
        $request = new ProcessImageRequest($request->all());

        if ($request->validate()) {

            $content = $this->service->processImage($request->all());
            $contentType = getimagesizefromstring($content)['mime'];

            return response()->stream(function() use ($content) {
                echo $content;
            }, 200, ['content-type' => $contentType]);
        }

        return $this->response->errorBadRequest();
    }

    public function uploadImages(Request $request)
    {
        $request = new UploadImagesRequest($request->all());

        if ($request->validate()) {

            return $this->response->array([
                'data' => $this->service->uploadImages($request->get('dealer_id'), $request->get('files'))
            ]);
        }

        return $this->response->errorBadRequest();
    }

    public function getImages(Request $request)
    {
        $request = new GetImagesRequest($request->all());

        if ($request->validate()) {

            return $this->response->array([
                'data' => $this->service->getImages($request->get('dealer_id'))
            ]);
        }

        return $this->response->errorBadRequest();
    }

    public function processHtml(Request $request)
    {
        $request = new ProcessHtmlRequest($request->all());

        if ($request->validate()) {

            if ($request->get('action') == 'download') {

                $headers = [
                    'content-disposition' => 'attachment; filename='. $request->get('filename'),
                    'content-type' => 'text/html',
                    'content-length' => strlen($request->get('html'))
                ];

                return response()->make($request->get('html'), 200, $headers);

            } elseif ($request->get('action') == 'email') {

                $this->service->send($request->all());

                return $this->successResponse();
            }
        }

        return $this->response->errorBadRequest();
    }

    public function getConfigs(Request $request)
    {
        $request = new GetImagesRequest($request->all());

        if ($request->validate()) {

            return $this->response->array([
                'data' => $this->service->getConfigs($request->get('dealer_id'))
            ]);
        }

        return $this->response->errorBadRequest();
    }
}