<?php

namespace App\Repositories\CRM\Report;

use App\Repositories\RepositoryAbstract;
use App\Models\CRM\Report\Report;
use Illuminate\Database\Eloquent\Collection;
use App\Models\CRM\Leads\Lead;
use App\Models\Website\Website;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\User\SalesPerson;
use App\Models\User\NewDealerUser;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\Attribute;
use App\Models\Inventory\AttributeValue;

/**
 * Class ReportRepository
 * @package App\Repositories\CRM\Report
 */
class ReportRepository extends RepositoryAbstract implements ReportRepositoryInterface 
{
    /**
     * @param array $params
     * @return Collection
     */
    public function getAll($params): Collection
    {
        if (!isset($params['user_id'])) {
            throw new RepositoryInvalidArgumentException('Missing user_id param. Params - ' . json_encode($params));
        }

        if (!isset($params['report_type'])) {
            throw new RepositoryInvalidArgumentException('Missing report_type param. Params - ' . json_encode($params));
        }

        return Report::where($params)->get();
    }

    /**
     * @param array $params
     * @return Collection
     */
    public function create($params): Collection
    {
        $report = new Report;
        $report->fill($params);

        $filters = [
            'p_start' => $params['p_start'],
            'p_end' => $params['p_end'],
            's_start' => $params['s_start'],
            's_end' => $params['s_end'],
            'chart_span' => $params['chart_span'],
            'lead_source' => $params['lead_source']
        ];

        if (isset($params['sales_people']) && !is_null($params['sales_people']) 
            && isset($params['lead_status']) && !is_null($params['lead_status'])) {
            $filters = array_merge($filters, [
                'sales_people' => $params['sales_people'], 
                'lead_status' => $params['lead_status']
            ]);
        }

        if (isset($params['brands']) && !is_null($params['brands']) 
            && isset($params['trailer_categories']) && !is_null($params['trailer_categories'])) {
            $filters = array_merge($filters, [
                'brands' => $params['brands'], 
                'trailer_categories' => $params['trailer_categories']
            ]);
        }

        $report->filters = json_encode($filters);

        $report->save();

        return $this->getAll([
            'user_id' => $params['user_id'],
            'report_type' => $params['report_type']
        ]);
    }

    /**
     * @param array $params
     * @return bool
     */
    public function delete($params)
    {
        return Report::where($params)->delete();
    }

    /**
     * @param int $id
     * @return array
     */
    public function find($id)
    {
        return Report::findOrFail($id);
    }

    /**
     * @param array $params
     * @return array
     */
    public function filterLeads(array $params)
    {
        $query = Lead::select( 
            LeadStatus::getTableName() .'.status', 
            Lead::getTableName() .'.identifier',
            LeadStatus::getTableName() .'.sales_person_id',
            Lead::getTableName() .'.date_submitted',
            Lead::getTableName() .'.lead_type',
            SalesPerson::getTableName() .'.first_name',
            SalesPerson::getTableName() .'.last_name',
        );

        $query = $query->leftJoin(LeadStatus::getTableName(), Lead::getTableName() . '.identifier', '=', LeadStatus::getTableName() .'.tc_lead_identifier');
        $query = $query->leftJoin(Website::getTableName(), Lead::getTableName() . '.website_id', '=', Website::getTableName() . '.id');
        $query = $query->leftJoin(NewDealerUser::getTableName(), Lead::getTableName() . '.dealer_id', '=', NewDealerUser::getTableName() . '.id');
        $query = $query->leftJoin(SalesPerson::getTableName(), LeadStatus::getTableName() . '.sales_person_id', '=', SalesPerson::getTableName() . '.id')
            ->whereNull(SalesPerson::getTableName() . '.deleted_at');

        $query = $query->where(Lead::getTableName() .'.dealer_id', $params['dealer_id'])
            ->where(Lead::getTableName() .'.is_spam', 0)
            ->whereNotNull(Lead::getTableName() .'.website_id')
            ->where(Lead::getTableName() .'.website_id', '<>', 0)
            ->where(Lead::getTableName() .'.date_submitted', '>=', $params['date_from'])
            ->where(Lead::getTableName() .'.date_submitted', '<=', $params['date_to']);

        if (isset($params['sales_people'])) {
            $query = $query->whereIn(LeadStatus::getTableName() .'.sales_person_id', $params['sales_people']);
        }

        if (isset($params['lead_source']) && $params['lead_source'] == self::LEAD_SOURCE_TRAILERTRADERS) {
            $query = $query->where(Lead::getTableName() .'.website_id', Website::TRAILERTRADER_ID);
        }

        if (isset($params['lead_status'])) {
            $query = $query->where(function($query) use ($params) {
                $query->whereIn(LeadStatus::getTableName() .'.status', $params['lead_status'])
                    ->orWhereNull(LeadStatus::getTableName() .'.status');
            });
        }

        $query = $query->groupBy(Lead::getTableName() . '.identifier')
            ->orderBy(Lead::getTableName() .'.date_submitted', 'DESC');

        return $query->get()->toArray();
    }

    /**
     * @param array $params
     * @return array
     */
    public function filterInventories(array $params)
    {
        $filterByPullType = isset($params['filter_by_pull_type']) ? 
            filter_var($params['filter_by_pull_type'], FILTER_VALIDATE_BOOLEAN) : false;

        if ($filterByPullType) {

            // build pull_type values
            $pullTypeValues = Attribute::select('values')
                ->where('code', self::INVENTORY_ATTRIBUTE_PULL_TYPE_CODE)->get()->first()->values;
    
            $rawPullTypes = explode(',', $pullTypeValues);
            $pullTypes = [];
            foreach ($rawPullTypes as $rawPullType) {
                list($pullTypeKey, $pullTypeValue) = explode(':', $rawPullType);
                $pullTypes[$pullTypeKey] = $pullTypeValue;
            }
    
            // start build query
            $query = AttributeValue::selectRaw('count(*) as cnt, '. AttributeValue::getTableName() .'.value AS pull_type')
                ->join(
                    Inventory::getTableName(), 
                    Inventory::getTableName() .'.inventory_id', 
                    '=', 
                    AttributeValue::getTableName() .'.inventory_id'
                );
    
            // build query conditions
            $query = $query->where(AttributeValue::getTableName(). '.attribute_id', Attribute::PULL_TYPE)
                ->where(AttributeValue::getTableName(). '.value', '<>', '')
                ->whereNotNull(AttributeValue::getTableName(). '.value');

        } else {
        
            // start build query
            $query = Inventory::select(
                Inventory::getTableName(). '.created_at',
                Inventory::getTableName(). '.manufacturer',
                Inventory::getTableName(). '.category',
                Inventory::getTableName(). '.condition',
            );
        }

        // Applying Common Inventory Filters
        $objectIsInventory = isset($params['object_is_inventory']) ?
            filter_var($params['object_is_inventory'], FILTER_VALIDATE_BOOLEAN) : true;

        if (!$objectIsInventory) {
            $query = $query->join(
                Lead::getTableName(), 
                Lead::getTableName() .'.inventory_id', 
                '=', 
                Inventory::getTableName() .'.inventory_id'
            );
        }

        $query = $query->where(Inventory::getTableName() .'.dealer_id', $params['dealer_id'])
            ->whereBetween(Inventory::getTableName() .'.created_at', [$params['date_from'], $params['date_to']]);

        if (isset($params['brands'])) {
            $query = $query->whereIn(Inventory::getTableName() .'.manufacturer', $params['brands']);
        }

        if (isset($params['categories'])) {
            $query = $query->whereIn(Inventory::getTableName() .'.category', $params['categories']);
        }

        if (!$objectIsInventory && isset($params['lead_source']) 
            && $params['lead_source'] == self::LEAD_SOURCE_TRAILERTRADERS) {
            $query = $query->where(Lead::getTableName() .'.website_id', Website::TRAILERTRADER_ID);
        }
        // END Common Inventory Filters

        if ($filterByPullType) {

            $query = $query->groupBy(AttributeValue::getTableName(). '.value');
    
            $inventories = $query->get()->toArray();

            // Replacing pull_type value in Inventory
            foreach ($inventories as $index => $inventory) {
                $inventories[$index]['pull_type'] = $pullTypes[$inventories[$index]['pull_type']];
            }
    
            return $inventories;

        } else {

            $query = $query->orderBy(Inventory::getTableName(). '.created_at');
    
            return $query->get()->toArray();
        }
    }
}