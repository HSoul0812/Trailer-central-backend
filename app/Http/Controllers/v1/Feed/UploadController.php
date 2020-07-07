<?php


namespace App\Http\Controllers\v1\Feed;


use App\Http\Controllers\RestfulController;
use App\Jobs\Import\Feed\DealerFeedImporterJob;
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
            // queue the data for processing; processing involves breaking up the data into
            //   individual transactions (addInventory, addDealer) and one object per row
            //   then a collector then later processes each row/object for importing
            $job = new DealerFeedImporterJob($json, $code, $feedUploader);

            Log::info('Dispatching a DealerFeedImporterJob', ['code' => $code]);
            $this->dispatch($job->onQueue('factory-feeds'));

            return new Response([
                'message' => 'Data has been received and is queued for processing.',
                'result' => true,
            ]);

        } catch (\Exception $e) {
            // return error
            Log::error("Exception: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);

            $this->response->errorBadRequest($e->getMessage());
        }
    }
}
