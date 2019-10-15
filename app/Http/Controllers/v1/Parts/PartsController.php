<?php

namespace App\Http\Controllers\v1\Parts;

use App\Http\Controllers\RestfulController;
use Illuminate\Support\Facades\Request;
use App\Exceptions\NotImplementedException;
use Laravel\Lumen\Routing\Controller;

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

    public function index(Request $request) {
        throw new NotImplementedException();
    }

    public function show($id) {
        throw new NotImplementedException();
    }

    public function update(Request $request) {
        throw new NotImplementedException();
    }

}
