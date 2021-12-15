<?php

namespace App\Repositories;

/**
 * Description of Repository
 *
 * @author Eczek
 */
interface Repository
{
    const SELECT = 'select';

    const CONDITION_AND_WHERE = 'andWhere';
    const CONDITION_AND_WHERE_RAW = 'andWhereRaw';
    const CONDITION_AND_WHERE_IN = 'andWhereIn';

    const RELATION_WITH_COUNT = 'withCount';

    const CONDITION_AND_HAVING_COUNT = 'andHavingCount';

    const GROUP_BY = 'groupBy';

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
