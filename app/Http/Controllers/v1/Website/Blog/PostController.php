<?php

namespace App\Http\Controllers\v1\Website\Blog;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Exceptions\NotImplementedException;
use App\Repositories\Website\Blog\PostRepositoryInterface;
use App\Http\Requests\Website\Blog\GetPostsRequest;
use App\Http\Requests\Website\Blog\CreatePostRequest;
use App\Http\Requests\Website\Blog\UpdatePostRequest;
use App\Http\Requests\Website\Blog\DeletePostRequest;
use App\Transformers\Website\Blog\PostTransformer;

class PostController extends RestfulController
{
    
    protected $posts;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PostRepositoryInterface $posts)
    {
        $this->posts = $posts;
    }
    
    /**
     * @OA\Put(
     *     path="/api/website/{websiteId}/blog/posts",
     *     description="Create a post",
     *     tags={"Post"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Post ID",
     *         required=true,
     *         @OA\Schema(@OA\Schema(type="integer"))
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Post title",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="post_content",
     *         in="query",
     *         description="Post content",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     * 
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of posts",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function create(Request $request) {
        $request = new CreatePostRequest($request->all());
        var_dump($request->validate());
        var_dump($request->all());
        if ( $request->validate() ) {
            return $this->response->item($this->posts->create($request->all()), new PostTransformer());
        }  
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Delete(
     *     path="/api/website/{websiteId}/blog/posts{id}",
     *     description="Delete a post",     
     *     tags={"Post"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Post ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Confirms post was deleted",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function destroy(int $id) {
        $request = new DeletePostRequest(['id' => $id]);
        
        if ( $request->validate() && $this->posts->delete(['id' => $id])) {
            return $this->response->noContent();
        }
        
        return $this->response->errorBadRequest();
    }
    

    /**
     * @OA\Get(
     *     path="/api/posts",
     *     description="Retrieve a list of posts",
     
     *     tags={"Post"},
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
     *     ),
     *     @OA\Parameter(
     *         name="type_id",
     *         in="query",
     *         description="Post types",
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
     *         in="query",
     *         description="Post categories",
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
     *         in="query",
     *         description="Post manufacturers",
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
     *         in="query",
     *         description="Post brands",
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
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Post IDs",
     *         required=false,
     *         @OA\Property(
     *            type="array",
     *            @OA\Items(
     *              type="array",
     *              @OA\Items()
     *            ),
     *            description="Post IDs array"
     *         )
     *     ),
     *   @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="Post price can be in format: [10 TO 100], [10], [10.0 TO 100.0]",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of posts",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function index(Request $request) {
        $request = new GetPostsRequest($request->all());
        
        if ( $request->validate() ) {
            $posts = $this->posts->getAll($request->all());
            return $this->response->paginator($posts, new PostTransformer());
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/website/{websiteId}/blog/posts{id}",
     *     description="Retrieve a post",
     
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
    public function show(int $id) {
        $request = new ShowPostRequest(['id' => $id]);
        
        if ( $request->validate() ) {
            return $this->response->item($this->posts->get(['id' => $id]), new PostTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
    
    /**
     * @OA\Post(
     *     path="/api/website/{websiteId}/blog/posts{id}",
     *     description="Update a post",
     * 
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Post ID",
     *         required=true,
     *         @OA\Schema(@OA\Schema(type="integer"))
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Post title",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="post_content",
     *         in="query",
     *         description="Post content",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     * 
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of posts",
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
        $request = new UpdatePostRequest($requestData);
        
        if ( $request->validate() ) {
            return $this->response->item($this->posts->update($request->all()), new PostTransformer());
        }
        
        return $this->response->errorBadRequest();
    }

}
