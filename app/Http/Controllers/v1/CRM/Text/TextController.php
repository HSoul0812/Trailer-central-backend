<?php

namespace App\Http\Controllers\v1\CRM\Text;

use App\Http\Controllers\Controller;
use App\Repositories\CRM\Text\TextRepositoryInterface;
use Dingo\Api\Http\Request;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\CRM\Text\GetTextsRequest;
use App\Http\Requests\CRM\Text\CreateTextRequest;
use App\Http\Requests\CRM\Text\ShowTextRequest;
use App\Http\Requests\CRM\Text\UpdateTextRequest;
use App\Http\Requests\CRM\Text\DeleteTextRequest;
use App\Transformers\CRM\Text\TextTransformer;
use Twilio\Rest\Client;

class TextController extends Controller
{
    use Helpers;

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
     *     path="/api/leads/{leadId}/texts",
     *     description="Retrieve a list of texts by lead id",
     *     tags={"Text"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Page Limit",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort order can be: price,-price,relevance,title,-title,length,-length",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     )
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of texts",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function index(Request $request) {
        $request = new GetTextsRequest($request->all());
        
        if ($request->validate()) {
            return $this->response->paginator($this->texts->getAll($request->all()), new TextTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
    
    /**
     * @OA\Put(
     *     path="/api/leads/{leadId}/texts",
     *     description="Create a text",
     *     tags={"Text"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Text ID",
     *         required=true,
     *         @OA\Schema(@OA\Schema(type="integer"))
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Text title",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="text_content",
     *         in="query",
     *         description="Text content",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     * 
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of texts",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function create(Request $request) {
        $request = new CreateTextRequest($request->all());
        if ( $request->validate() ) {
            // Create Text
            return $this->response->item($this->texts->create($request->all()), new TextTransformer());
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/leads/{leadId}/texts/{id}",
     *     description="Retrieve a text",
     
     *     tags={"Post"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Post ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a post",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function show(int $leadId, int $id) {
        $request = new ShowTextRequest(['id' => $id]);
        
        if ( $request->validate() ) {
            return $this->response->item($this->texts->get(['id' => $id]), new TextTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
    
    /**
     * @OA\Text(
     *     path="/api/leads/{leadId}/texts/{id}",
     *     description="Update a text",
     * 
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Text ID",
     *         required=true,
     *         @OA\Schema(@OA\Schema(type="integer"))
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Text title",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="text_content",
     *         in="query",
     *         description="Text content",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     * 
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of texts",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function update(int $leadId, int $id, Request $request) {
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new UpdateTextRequest($requestData);
        
        if ( $request->validate() ) {
            return $this->response->item($this->texts->update($request->all()), new TextTransformer());
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Delete(
     *     path="/api/leads/{leadId}/texts/{id}",
     *     description="Delete a text",     
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
    public function destroy(int $leadId, int $id) {
        $request = new DeleteTextRequest(['id' => $id]);
        
        if ( $request->validate()) {
            // Create Text
            return $this->response->item($this->texts->delete(['id' => $id]), new TextTransformer());
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Stop(
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
    public function Stop(int $leadId, int $id) {
        $request = new StopTextRequest(['id' => $id]);
        
        if ( $request->validate()) {
            // Stop Text
            return $this->response->item($this->texts->stop(['id' => $id]), new TextTransformer());
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Post(path="/leads/{leadId}/texts/send",
     *     tags={"interactions"},
     *     summary="Send interaction text",
     *     description="",
     *     operationId="sendText",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="user_id",
     *                     description="User Authentication ID.",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="lead_id",
     *                     description="Lead ID.",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="body",
     *                     description="Email body.",
     *                     type="string",
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email sent successfully",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="success",
     *                         type="bool",
     *                         description="The response success"
     *                     ),
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="The response message"
     *                     ),
     *                     example={
     *                         "success": true,
     *                         "message": "Email sent successfully",
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found | Lead not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="error",
     *                         type="bool",
     *                         description="The response error"
     *                     ),
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="The response message"
     *                     ),
     *                     example={
     *                         "error": true,
     *                         "message": "User not found",
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Throwable message",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="error",
     *                         type="bool",
     *                         description="The response success"
     *                     ),
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="The response message"
     *                     ),
     *                     example={
     *                         "error": true,
     *                         "message": "Throwable message",
     *                     }
     *                 )
     *             )
     *         }
     *      ),
     * )
     */
    public function sendText(Request $request)
    {
        $request = new SendTextRequest($request->all());
        
        if ( $request->validate()) {
            // Get Params
            $params = $request->all();
            $phone = $params['phone'];

            // Initialize Twilio Client
            $client = new Client(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));

            // Find Lead ID
            $lead = Lead::findOrFail($request->input('lead_id'));
            $locationId = $lead->dealer_location_id;
            $dealer = $lead->dealer();

            // Initialize Text Form
            $to_number = '+' . ((strlen($phone) === 11) ? $phone : '1' . $phone);
            $phoneRouter = new \Interactions\PhoneRouter($lead->dealer_id, $locationId, $db, $to_number, '', $client);
            $from_number = $phoneRouter->getDealerNumber();

            // Look Up To Number
            $carrier = $client->lookups->v1->phoneNumbers($to_number)->fetch(array("type" => array("carrier")))->carrier;
            if (empty($carrier['mobile_country_code'])) {
                return response()->json([
                    'status' => 'landline',
                    'message' => 'Error: The number provided is a landline and cannot receive texts!'
                ], 500);
            }

            // Send Text
            return $this->response->item($this->texts->create($request->all()), new TextTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
}
