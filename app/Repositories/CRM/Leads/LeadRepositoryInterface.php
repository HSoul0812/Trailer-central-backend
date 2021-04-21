<?php

namespace App\Repositories\CRM\Leads;

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

    public function getLeadsWithoutCustomers(callable $callback = null, $chunkSize = 1000);

}
