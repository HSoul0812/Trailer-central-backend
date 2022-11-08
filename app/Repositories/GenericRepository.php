<?php

namespace App\Repositories;

/**
 * Interface GenericRepository
 *
 * A generic repository does not implement all CRUD operations; it is
 *
 * @package App\Repositories
 */
interface GenericRepository
{
    public const SELECT = 'select';

    public const CONDITION_AND_WHERE = 'andWhere';
    public const CONDITION_AND_WHERE_RAW = 'andWhereRaw';
    public const CONDITION_AND_WHERE_IN = 'andWhereIn';
    public const CONDITION_AND_WHERE_NOT_IN = 'andWhereNotIn';

    public const RELATION_WITH_COUNT = 'withCount';

    public const CONDITION_AND_HAVING_COUNT = 'andHavingCount';

    public const GROUP_BY = 'groupBy';
}
