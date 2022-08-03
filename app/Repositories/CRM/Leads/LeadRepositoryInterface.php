<?php

namespace App\Repositories\CRM\Leads;

use App\Models\CRM\Leads\Lead;
use App\Repositories\Repository;

use Illuminate\Database\Eloquent\Collection;

interface LeadRepositoryInterface extends Repository {
    /**
     * Get All Unassigned Leads
     *
     * @param int $params
     * @return type
     */
    public function getAllUnassigned($params);

    /**
     * Get Leads By Emails
     *
     * @param int $dealerId
     * @param array $emails
     * @return Collection of Lead
     */
    public function getByEmails(int $dealerId, array $emails);

    /**
     * Find Existing Lead That Matches Current Lead!
     *
     * @param array $params
     * @return Collection<Lead>
     */
    public function findAllMatches(array $params): Collection;

    /**
     * Create Assign Log for Lead
     *
     * @param type $params
     * @return type
     */
    public function assign($params);

    /**
     * Returns array in the following format:
     *
     * [
     *    'open' => 123,
     *    'closed_won' => 123,
     *    'closed_lost' => 123,
     *    'hot' => 123
     * ]
     *
     * @param int $dealerId
     * @param array $params optional filters
     * @return array
     */
    public function getLeadStatusCountByDealer($dealerId, $params = []);

    /**
     * Returns customers based on leads
     *
     * @param array $params optional filters
     * @return Collection
     */
    public function getCustomers($params = []);

    /**
     * Returns list of available sort fields
     *
     * @return array
     */
    public function getLeadsSortFields();

    /**
     * Returns list of public sort fields for the CRM
     *
     * @return array
     */
    public function getLeadsSortFieldsCrm(): array;

    /**
     * @param callable|null $callback
     * @param int $chunkSize
     * @return mixed
     */
    public function getLeadsWithoutCustomers(callable $callback = null, $chunkSize = 1000);

    /**
     * @param array $params
     * @return mixed
     */
    public function getUniqueFullNames(array $params);

    /**
     * @param array $params
     * @return Lead|null
     */
    public function first(array $params): ?Lead;
}
