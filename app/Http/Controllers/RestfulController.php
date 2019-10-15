<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;

/**
 * 
 * @author Eczek
 */
abstract class RestfulController {

    /**
     * @OA\Info(
     *   title="TC API",
     *   version="1.0",
     *   @OA\Contact(
     *     email="alberto@trailercentral.com",
     *     name="Trailercentral"
     *   )
     * )
     */
   
    /**
     * Displays a list of all records in the DB. 
     * Paginated or not paginated
     */
    public function index(Request $request);
    
    /**
     * Stores a record in the DB
     * 
     * @param Request $request
     */
    public function create(Request $request);
    
    /**
     * Display data about the record in the DB
     * 
     * @param int $id
     */
    public function show($id);
    
    /**
     * Updates the record data in the DB
     * 
     * @param Request $request
     */
    public function update(Request $request);
    
    /**
     * Deletes the record in the DB
     * 
     * @param int $id
     */
    public function destroy($id);
    
}
