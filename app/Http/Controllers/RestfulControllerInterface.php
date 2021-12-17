<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\UpdateRequestInterface;

interface RestfulControllerInterface
{
    /**
     * Displays a list of all records in the DB.
     * Paginated or not paginated.
     */
    public function index(IndexRequestInterface $request);

    /**
     * Stores a record in the DB.
     */
    public function create(CreateRequestInterface $request);

    /**
     * Display data about the record in the DB.
     */
    public function show(int $id);

    /**
     * Updates the record data in the DB.
     */
    public function update(int $id, UpdateRequestInterface $request);

    /**
     * Deletes the record in the DB.
     */
    public function destroy(int $id);
}
