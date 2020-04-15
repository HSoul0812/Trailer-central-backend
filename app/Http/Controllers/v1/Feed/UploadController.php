<?php


namespace App\Http\Controllers\v1\Feed;


use App\Http\Controllers\RestfulController;
use App\Services\Import\Feed\DealerFeedUploaderService;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Class UploadController
 *
 * For APIs for upload of dealer data
 *
 * @package App\Http\Controllers\v1\Uploader
 */
class UploadController extends RestfulController
{

    /**
     * Upload source data
     *
     * @param Request $request
     * @param string $code
     * @param DealerFeedUploaderService $feedUploader
     * @return \Dingo\Api\Http\Response|void
     *
     * @QA\Post(
     *     path="/api/feed/uploader/{code}",
     *     description="Upload source data",
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="Page Limit",
     *         required=false
     *     )
     * )
     */
    public function upload(Request $request, string $code, DealerFeedUploaderService $feedUploader)
    {
        $json = $request->all();

        try {
            $result = $feedUploader->run($json, $code);

            // return status
//            return $this->response->item([
//                'result' => $result,
//            ]);

            return new Response([
                'result' => $result,
            ]);

        } catch (\Exception $e) {
            // return error
            Log::error("Exception: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);

            return $this->response->errorBadRequest($e->getMessage());
        }
    }
}
