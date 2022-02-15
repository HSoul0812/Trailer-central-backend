<?php


namespace App\Repositories\Parts;


use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

interface AuditLogRepositoryInterface extends Repository
{
    /**
     * Gets Modified Parts By Date
     * 
     * @param Carbon\CarbonImmutable $date
     * @param int $dealerId
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getByDate(Carbon $date, int $dealerId) : Collection;
    
    /**
     * Gets Modified Parts By Year
     * 
     * @param int $year
     * @param int $dealerId
     * 
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getByYear(int $year, int $dealerId) : Builder;
    
    /**
     * Does the same thing as getByYear but saves to CSV and returns the path
     * 
     * @param int $year
     * @param int $dealerId
     * 
     * @return array
     */
    public function getByYearCsv(int $year, int $dealerId) : array;
}
