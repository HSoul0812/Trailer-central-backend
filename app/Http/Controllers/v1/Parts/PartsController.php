<?php

namespace App\Http\Controllers\v1\Parts;

use App\Http\Controllers\RestfulController;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Support\Facades\Request;
use App\Exceptions\NotImplementedException;

class PartsController extends Controller implements RestfulController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function create(Request $request) {
        throw new NotImplementedException();
    }

    public function destroy($id) {
        throw new NotImplementedException();
    }

    /**
     * @OA\Get(
     *     path="/parts",
     *     @OA\Response(
     *         response="200",
     *         description="Returns json with success: true",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function index(Request $request) {
        return response()->json([ 'success' => true ]);
    }

    public function show($id) {
        throw new NotImplementedException();
    }

    public function update(Request $request) {
        throw new NotImplementedException();
    }

}
