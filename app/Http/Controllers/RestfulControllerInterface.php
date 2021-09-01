<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\UpdateRequestInterface;
use App\Http\Requests\RequestInterface;

interface RestfulControllerInterface {

    /**
     * Displays a list of all records in the DB.
     * Paginated or not paginated
     * 
     * @param IndexRequestInterface $request
     */
    public function index(IndexRequestInterface $request);

    /**
     * Stores a record in the DB
     *
     * @param CreateRequestInterface $request
     */
    public function create(CreateRequestInterface $request);

    /**
     * Display data about the record in the DB
     *
     * @param int $id
     */
    public function show(int $id);
    
    /**
     * Updates the record data in the DB
     *
     * @param int $id
     * @param UpdateRequestInterface $request
     */
    public function update(int $id, UpdateRequestInterface $request);

    /**
     * Deletes the record in the DB
     *
     * @param int $id
     */
    public function destroy(int $id);
}
