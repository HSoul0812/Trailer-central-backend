<?php

namespace App\Repositories\Website\PaymentCalculator;

use App\Jobs\Website\PaymentCalculatorReIndexJob;
use App\Models\Website\PaymentCalculator\Settings;
use App\Models\Website\Website;
use Illuminate\Database\Query\Builder;

class SettingsRepository implements SettingsRepositoryInterface {

    public function create($params) {

        $settings = Settings::create($params);

        $dealer = Website::find($settings->website_id)->dealer_id;
        dispatch(new PaymentCalculatorReIndexJob([$dealer]));

        return $settings;
    }

    /**
     * @param array $params `id` parameter is required
     * @return bool
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function delete($params): bool
    {
        if (!isset($params['id'])) {
            throw new \InvalidArgumentException(__CLASS__ . ' require `id` param');
        }

        /** @var Settings $settings */
        $settings = Settings::findOrFail($params['id']);

        return (bool)$settings->delete();
    }

    public function get($params) {
        return Settings::where('website_id', $params['website_id'])->firstOrFail();
    }

    /**
     * Usually, it should return a config payment calculator list, However, when the inventory price has been provided,
     * it should determine which is the correct config payment calculator
     *
     * @param $params
     * @return \Illuminate\Support\Collection
     */
    public function getAll($params) {
        /** @var Builder $query */
        $query = Settings::with('entityType')->where('website_id', $params['website_id']);

        if (isset($params['entity_type_id'])) {
            $query->where('entity_type_id', $params['entity_type_id']);
        }

        if (isset($params['inventory_condition'])) {
            $query->where('inventory_condition', $params['inventory_condition']);
        }

        if (isset($params['financing'])) {
            $query->where('financing', $params['financing']);
        }

        if (isset($params['inventory_price'])) {
            /** @var \Illuminate\Database\Eloquent\Builder $queryOver */
            /** @var \Illuminate\Database\Eloquent\Builder $queryLessThan */
            $queryOver = clone $query;
            $queryLessThan = clone $query;

            // our MySQL version doesn't allow LIMIT & IN/ALL/ANY/SOME sub-query, so we need to get the ids
            $queryOver->select('id')
                ->where('operator', Settings::OPERATOR_OVER)
                ->where('inventory_price', '<', $params['inventory_price'])
                ->orderBy('inventory_price', 'desc')
                ->limit(1);

            $queryLessThan->select('id')
                ->where('operator', Settings::OPERATOR_LESS_THAN)
                ->where('inventory_price', '>', $params['inventory_price'])
                ->orderBy('inventory_price')
                ->limit(1);

            $leftAndRightIdBounds = array_merge($queryOver->get()->toArray(), $queryLessThan->get()->toArray());

            if (!empty($leftAndRightIdBounds)) {
                $query->whereIn('id', $leftAndRightIdBounds);
            }

            // when we have a discrepancy, then we need to pick the most profitable loan for the company
            $query->limit(1)->orderBy('months', 'desc');
        }

        return $query->get();
    }

    public function update($params) {
        $settings = Settings::findOrFail($params['id']);
        $settings->fill($params);
        $settings->save();

        $dealer = Website::find($settings->website_id)->dealer_id;
        dispatch(new PaymentCalculatorReIndexJob([$dealer]));

        return $settings;
    }

    public function getCalculatedSettings($params): array
    {
        $calculatorSettings = $params;
        $inventoryPrice = $calculatorSettings['inventory_price'];

        if ($calculatorSettings['inventory_price'] > 0 ) {

            $settingFinancing = $this->getAll(array_merge($calculatorSettings + ['financing']));
            $settingNoFinancing = $this->getAll(array_merge($calculatorSettings + ['no_financing']));

            if ( ($settingFinancing->operator == 'less_than' && $settingNoFinancing->operator == 'less_than') ) {
                if ($inventoryPrice < $settingFinancing->inventory_price) {
                    $setting = $settingNoFinancing;
                } else {
                    $setting = $settingFinancing;
                }
            } else if ( ($settingFinancing->operator == 'over' && $settingNoFinancing->operator == 'over') ) {
                if ($inventoryPrice > $settingFinancing->inventory_price) {
                    $setting = $settingNoFinancing;
                } else {
                    $setting = $settingFinancing;
                }
            } else {
                $setting = $settingFinancing;
            }

            if ($setting->financing == 'no_financing' || $setting === false) {
                return false;
            }

            $priceDown = (double)($setting->down / 100) * $inventoryPrice;
            $principal = $inventoryPrice - $priceDown;
            $interest = (double)$setting->apr / 100 / 12;
            $payments = $setting->months;
            $compInterest = pow(1 + $interest, $payments);
            $monthlyPayment = number_format((float)($principal * $compInterest * $interest) / ($compInterest - 1), 2, '.', '');

            return [
                'apr' => $setting->apr,
                'down' => $priceDown,
                'years' => $setting->months / 12,
                'months' => $setting->months,
                'monthly_payment' => abs($monthlyPayment),
                'down_percentage' => $setting->down
            ];
        }

        return [];
    }
}
