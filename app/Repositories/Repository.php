<?php

namespace App\Repositories;

/**
 * Description of Repository
 *
 * @author Eczek
 */
interface Repository extends GenericRepository
{
    /**
     * Creates the record
     *
     * @return mixed
     */
    public function create($params);

    /**
     * Updates the record
     *
     * @param array $params
     * @return bool
     */
    public function update($params);

    /**
     * Retrieves the record
     *
     * @param array $params
     * @return mixed
     */
    public function get($params);

    /**
     * Deletes the record
     *
     * @param array $params
     * @return bool
     */
    public function delete($params);

    /**
     * Gets all records by $params
     *
     * @param array $params
     */
    public function getAll($params);
}
