<?php

namespace App\Repositories;

/**
 * Description of Repository
 *
 * @author Eczek
 */
interface Repository {

    const CONDITION_AND_WHERE = 'andWhere';

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

    /**
     * Get group info of records filtered by $params
     *
     * @param array $params
     */
    public function group($params);
}
