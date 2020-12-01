<?php

namespace App\Repositories\CRM\Customer;

use App\Exceptions\Dms\CustomerAlreadyExistsException;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\User\Customer;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class CustomerRepository implements CustomerRepositoryInterface
{
    /**
     * list if ES index fields that have a 'keyword' field
     */
    private $indexKeywordFields = [
        'display_name' => 'display_name.keyword',
        'first_name' => 'first_name.keyword',
        'last_name' => 'last_name.keyword',
        'email' => 'email.keyword',
    ];

    public function create($params)
    {
        $customer = new Customer($params);
        $customer->save();
        return $customer;
    }

    public function delete($params) {
        throw NotImplementedException;
    }

    public function get($params) {
        throw NotImplementedException;
    }

    public function getAll($params) {
        throw NotImplementedException;
    }

    public function update($params)
    {
        $customer = Customer::find($params['id']);
        $customer->fill(Arr::except($params, 'id'));
        $customer->save();
        return $customer;
    }

    public function getCustomersWihOpenBalance($dealerId, $perPage = 15) {
         $query = Customer::where('dealer_id', $dealerId)->has('openQuotes');
         return $query->get();
    }

    /**
     * @param  Lead  $lead
     * @param  bool  $useExisting Force use an existing customer record
     * @return Customer
     * @throws \Exception
     */
    public function createFromLead(Lead $lead, $useExisting = true)
    {
        if (empty($lead->first_name) || empty($lead->last_name)) {
            throw new \Exception('Lead first name or last name is empty');
        }

        if ($useExisting) {
            // match by dealer_id, and name
            //   erroneous matches are accepted because the dealer will create the
            //   required customer anyway if it does not exist
            $customer = Customer::where([
                'dealer_id' => $lead->dealer_id,
                'first_name' => trim($lead->first_name),
                'last_name' => trim($lead->last_name),
            ])->get()->first();

            if ($customer) {
                return $customer;
            }
        }

        $customer = new Customer([
            'first_name' => trim($lead->first_name),
            'last_name' => trim($lead->last_name),
            'display_name' => trim($lead->first_name) . ' ' . trim($lead->last_name),
            'email' => $lead->email_address,
            'drivers_license' => '',
            'home_phone' => $lead->phone_number,
            'work_phone' => $lead->phone_number,
            'cell_phone' => $lead->phone_number,
            'address' => $lead->address,
            'city' => $lead->city,
            'region' => $lead->state,
            'postal_code' => $lead->zip,
            'country' => 'US',
            'website_lead_id' => $lead->identifier,
            'tax_exempt' => 0,
            'is_financing_company' => 0,
            'account_number' => null,
            'gender' => null,
            'dob' => null,
            'deleted_at' => null,
            'is_wholesale' => 0,
            'default_discount_percent' => 0,
            'middle_name' => '',
            'company_name' => null,
            'use_same_address' => 1,
            'shipping_address' => $lead->address,
            'shipping_city' => $lead->city,
            'shipping_region' => $lead->state,
            'shipping_postal_code' => $lead->zip,
            'shipping_country' => 'US',
            'county' => null,
            'shipping_county' => null,
        ]);
        $customer->dealer_id = $lead->dealer_id;
        $customer->save();

        return $customer;
    }

    /**
     * @param $query
     * @param $dealerId
     * @param  array  $options Options: allowAll
     * @param  LengthAwarePaginator|null  $paginator Put a avr here, it will be given a paginator if `page` param is set
     * @return mixed
     * @throws \Exception
     */
    public function search($query, $dealerId, $options = [], &$paginator = null)
    {
        $search = Customer::boolSearch();

        if ($query['query'] ?? null) { // if a query is specified
            $search->must('multi_match', [
                'query' => $query['query'],
                'fuzziness' => 'AUTO',
                'fields' => ['display_name^2', 'first_name', 'last_name', 'email']
            ]);

        } else if ($options['allowAll'] ?? false) { // if no query supplied but is allowed
            $search->must('match_all', []);

        } else {
            throw new \Exception('Query is required');
        }

        // filter by dealer
        $search->filter('term', ['dealer_id' => $dealerId]);

        // sort order
        if ($query['sort'] ?? null) {
            $sortDir = substr($query['sort'], 0, 1) === '-'? 'asc': 'desc';
            $field = str_replace('-', '', $query['sort']);
            if (array_key_exists($field, $this->indexKeywordFields)) {
                $field = $this->indexKeywordFields[$field];
            }

            $search->sort($field, $sortDir);
        }

        // load relations
        // $search->load([]);

        // if a paginator is requested
        if ($options['page'] ?? null) {
            $page = $options['page'];
            $perPage = $options['per_page'] ?? 10;

            $search->from(($page - 1) * $perPage);
            $search->size($perPage);

            $searchResult = $search->execute();

            $paginator = new LengthAwarePaginator(
                $searchResult->models(),
                $searchResult->total(),
                $perPage,
                $page,
                [
                    'path' => Paginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ]
            );

            return $searchResult->models();
        }

        // if no paginator, set a default return size
        $size = $options['size'] ?? 50;
        $search->size($size);

        return $search->execute()->models();
    }

}
