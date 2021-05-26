<?php

namespace App\Http\Controllers\v1\CRM\Text;

use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\CRM\Text\TextRepositoryInterface;
use Dingo\Api\Http\Request;
use App\Http\Requests\CRM\Text\StopTextRequest;
use App\Transformers\CRM\Text\StopTransformer;

class StopController extends RestfulControllerV2
{
    protected $texts;

    /**
     * Create a new controller instance.
     *
     * @param Repository $texts
     */
    public function __construct(TextRepositoryInterface $texts)
    {
        $this->texts = $texts;
    }


    /**
     * @OA\Get(
     *     path="/api/leads/{leadId}/texts/{id}/stop",
     *     description="Stop sending future texts to this number",
     *     tags={"Text"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Text ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Confirms text was deleted",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function index(Request $request) {
        $request = new StopTextRequest($request->all());
        
        if ( $request->validate()) {
            // Stop Text
            return $this->response->item($this->texts->stop($request->all()), new StopTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
}
