<?php


namespace App\Repositories;


use App\Exceptions\NotImplementedException;

abstract class RepositoryAbstract
{
    /**
     * find records; similar to findBy()
     */
    public function get($params)
    {
        throw new NotImplementedException();
    }

    /**
     * find a single entity by primary key
     */
    public function find($id)
    {
        throw new NotImplementedException();
    }
    public function create($params)
    {
        throw new NotImplementedException();
    }

    public function update($params)
    {
        throw new NotImplementedException();
    }

    public function delete($params)
    {
        throw new NotImplementedException();
    }

    public function getAll($params)
    {
        throw new NotImplementedException();
    }

}
