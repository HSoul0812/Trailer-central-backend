<?php

namespace App\Http\Controllers\v1\Parts;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Exceptions\NotImplementedException;
use App\Repositories\Parts\PartRepositoryInterface;
use App\Http\Requests\Parts\CreatePartRequest;
use App\Http\Requests\Parts\DeletePartRequest;
use App\Transformers\Parts\PartsTransformer;
use App\Http\Requests\Parts\ShowPartRequest;
use App\Http\Requests\Parts\GetPartsRequest;
use App\Http\Requests\Parts\UpdatePartRequest;

class PartsController extends RestfulController
{
    
    protected $parts;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PartRepositoryInterface $parts)
    {
        $this->parts = $parts;
    }
    
    /**
     * @OA\Put(
     *     path="/api/parts/",
     *     description="Create a part",
     *     tags={"Parts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Part ID",
     *         required=true,
     *         @OA\Schema(@OA\Schema(type="integer"))
     *     ),
     *     @OA\Parameter(
     *         name="vendor_id",
     *         in="query",
     *         description="Vendor ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_specific_id",
     *         in="query",
     *         description="Vehicle Specific ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),     * 
     *     @OA\Parameter(
     *         name="brand_id",
     *         in="query",
     *         description="Part brand",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),          
     *    @OA\Parameter(
     *         name="manufacturer_id",
     *         in="query",
     *         description="Part manufacturers",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="type_id",
     *         in="query",
     *         description="Part type",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *   @OA\Parameter(
     *         name="category_id",
     *         in="path",
     *         description="Part category",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *    @OA\Parameter(
     *         name="qb_id",
     *         in="query",
     *         description="Part quickbooks id",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="subcategory",
     *         in="query",
     *         description="Part subcategory",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *   @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Part title",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sku",
     *         in="query",
     *         description="Part sku",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="Part price",
     *         required=false,
     *         @OA\Schema(type="numeric")
     *     ),
     *     @OA\Parameter(
     *         name="dealer_cost",
     *         in="query",
     *         description="Part dealer cost",
     *         required=false,
     *         @OA\Schema(type="numeric")
     *     ),
     *     @OA\Parameter(
     *         name="msrp",
     *         in="query",
     *         description="Part msrp",
     *         required=false,
     *         @OA\Schema(type="numeric")
     *     ),
     *     @OA\Parameter(
     *         name="weight",
     *         in="query",
     *         description="Part weight",
     *         required=false,
     *         @OA\Schema(type="numeric")
     *     ),
     *     @OA\Parameter(
     *         name="weight_rating",
     *         in="query",
     *         description="Part weight rating",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         description="Part description",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="qty",
     *         in="query",
     *         description="Part qty",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="show_on_website",
     *         in="query",
     *         description="Part show on website",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="is_vehicle_specific",
     *         in="query",
     *         description="Part vehicle specific",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_make",
     *         in="query",
     *         description="Part vehicle make",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_model",
     *         in="query",
     *         description="Part vehicle model",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_year_from",
     *         in="query",
     *         description="Part vehicle year from",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_year_to",
     *         in="query",
     *         description="Part vehicle year to",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="video_embed_code",
     *         in="query",
     *         description="Video embed code",
     *         required=false,
     *         @OA\Schema(type="text")
     *     ),
     *    @OA\Parameter(
     *         name="images",
     *         in="query",
     *         description="Part images",
     *         required=false,
     *         @OA\Property(
     *            type="array",
     *            @OA\Items(
     *              type="array",
     *              @OA\Items()
     *            ),
     *            description="Image URL array"
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of parts",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function create(Request $request) {
        $request = new CreatePartRequest($request->all());
        
        if ( $request->validate() ) {
            return $this->response->item($this->parts->create($request->all()), new PartsTransformer());
        }  
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Delete(
     *     path="/api/parts/{id}",
     *     description="Delete a part",     
     *     tags={"Parts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Part ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Confirms part was deleted",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function destroy(int $id) {
        $request = new DeletePartRequest(['id' => $id]);
        
        if ( $request->validate() && $this->parts->delete(['id' => $id])) {
            return $this->response->noContent();
        }
        
        return $this->response->errorBadRequest();
    }
    

    /**
     * @OA\Get(
     *     path="/api/parts",
     *     description="Retrieve a list of parts",
     
     *     tags={"Parts"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="path",
     *         description="Page Limit",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="path",
     *         description="Sort order can be: price,-price,relevance,title,-title,length,-length",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="type_id",
     *         in="path",
     *         description="Part types",
     *         required=false,
     *         @OA\Property(
     *            type="array",
     *            @OA\Items(
     *              type="array",
     *              @OA\Items()
     *            ),
     *            description="Type ID arra"
     *         )
     *     ),     
     *     @OA\Parameter(
     *         name="category_id",
     *         in="path",
     *         description="Part categories",
     *         required=false,
     *          @OA\Property(
     *            type="array",
     *            @OA\Items(
     *              type="array",
     *              @OA\Items()
     *            ),
     *            description="Category ID array"
     *         )
     *     ),
     *    @OA\Parameter(
     *         name="manufacturer_id",
     *         in="path",
     *         description="Part manufacturers",
     *         required=false,
     *         @OA\Property(
     *            type="array",
     *            @OA\Items(
     *              type="array",
     *              @OA\Items()
     *            ),
     *            description="Manufacturer ID array"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="brand_id",
     *         in="path",
     *         description="Part brands",
     *         required=false,
     *         @OA\Property(
     *            type="array",
     *            @OA\Items(
     *              type="array",
     *              @OA\Items()
     *            ),
     *            description="Brand ID array"
     *         )
     *     ),
     *   @OA\Parameter(
     *         name="price",
     *         in="path",
     *         description="Part price can be in format: [10 TO 100], [10], [10.0 TO 100.0]",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of parts",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function index(Request $request) {
        $request = new GetPartsRequest($request->all());
        
        if ( $request->validate() ) {
            return $this->response->paginator($this->parts->getAll($request->all()), new PartsTransformer());
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/parts/{id}",
     *     description="Retrieve a part",
     
     *     tags={"Parts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Part ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a part",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function show(int $id) {
        $request = new ShowPartRequest(['id' => $id]);
        
        if ( $request->validate() ) {
            return $this->response->item($this->parts->get(['id' => $id]), new PartsTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
    
    /**
     * @OA\Post(
     *     path="/api/parts/{id}",
     *     description="Update a part",
     
     *     tags={"Parts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Part ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="vendor_id",
     *         in="query",
     *         description="Vendor ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_specific_id",
     *         in="query",
     *         description="Vehicle Specific ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),     * 
     *     @OA\Parameter(
     *         name="brand_id",
     *         in="query",
     *         description="Part brand",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),          
     *    @OA\Parameter(
     *         name="manufacturer_id",
     *         in="query",
     *         description="Part manufacturers",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="type_id",
     *         in="query",
     *         description="Part type",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *   @OA\Parameter(
     *         name="category_id",
     *         in="path",
     *         description="Part category",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *    @OA\Parameter(
     *         name="qb_id",
     *         in="query",
     *         description="Part quickbooks id",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="subcategory",
     *         in="query",
     *         description="Part subcategory",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *   @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Part title",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sku",
     *         in="query",
     *         description="Part sku",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="Part price",
     *         required=false,
     *         @OA\Schema(type="numeric")
     *     ),
     *     @OA\Parameter(
     *         name="dealer_cost",
     *         in="query",
     *         description="Part dealer cost",
     *         required=false,
     *         @OA\Schema(type="numeric")
     *     ),
     *     @OA\Parameter(
     *         name="msrp",
     *         in="query",
     *         description="Part msrp",
     *         required=false,
     *         @OA\Schema(type="numeric")
     *     ),
     *     @OA\Parameter(
     *         name="weight",
     *         in="query",
     *         description="Part weight",
     *         required=false,
     *         @OA\Schema(type="numeric")
     *     ),
     *     @OA\Parameter(
     *         name="weight_rating",
     *         in="query",
     *         description="Part weight rating",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         description="Part description",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="qty",
     *         in="query",
     *         description="Part qty",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="show_on_website",
     *         in="query",
     *         description="Part show on website",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="is_vehicle_specific",
     *         in="query",
     *         description="Part vehicle specific",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_make",
     *         in="query",
     *         description="Part vehicle make",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_model",
     *         in="query",
     *         description="Part vehicle model",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_year_from",
     *         in="query",
     *         description="Part vehicle year from",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_year_to",
     *         in="query",
     *         description="Part vehicle year to",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="video_embed_code",
     *         in="query",
     *         description="Video embed code",
     *         required=false,
     *         @OA\Schema(type="text")
     *     ),
     *    @OA\Parameter(
     *         name="images",
     *         in="query",
     *         description="Part images",
     *         required=false,
     *          @OA\Property(
     *            type="array",
     *            @OA\Items(
     *              type="array",
     *              @OA\Items()
     *            ),
     *            description="Image URL array"
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of parts",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function update(int $id, Request $request) {
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new UpdatePartRequest($requestData);
        
        if ( $request->validate() ) {
            return $this->response->item($this->parts->update($request->all()), new PartsTransformer());
        }
        
        return $this->response->errorBadRequest();
    }

}
