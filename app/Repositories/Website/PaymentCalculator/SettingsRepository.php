<?php

namespace App\Repositories\Website\PaymentCalculator;

use App\Models\Inventory\Inventory;
use App\Models\Website\Config\WebsiteConfig;
use App\Models\Website\Config\WebsiteConfigDefault;
use App\Models\Website\PaymentCalculator\Settings;
use App\Repositories\Website\Config\DefaultConfigRepository;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;

class SettingsRepository implements SettingsRepositoryInterface
{
    public function create($params)
    {
        return Settings::create($params);
    }

    /**
     * @param array $params `id` parameter is required
     *
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

    public function get($params)
    {
        return Settings::where('website_id', $params['website_id'])->firstOrFail();
    }

    /**
     * Usually, it should return a config payment calculator list, However, when the inventory price has been provided,
     * it should determine which is the correct config payment calculator
     *
     * @param $params
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAll($params)
    {
        /** @var Builder $query */
        $query = Settings::with('entityType')->where('website_id', $params['website_id']);

        if (isset($params['entity_type_id'])) {
            $query->where('entity_type_id', $params['entity_type_id']);
        }

        if (isset($params['inventory_category_id_or_null'])) {
            $query->where(function ($q) use ($params) {
                $q->whereNull('inventory_category_id')
                    ->orWhere('inventory_category_id', $params['inventory_category_id_or_null']);
            });
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

    public function update($params)
    {
        /** @var Settings $settings */
        $settings = Settings::findOrFail($params['id']);
        $settings->fill($params);
        $settings->save();

        return $settings;
    }

    /**
     * @param Inventory $inventory
     *
     * @return array{apr: float, down: float, years: int, months: int, monthly_payment: float, down_percentage:float}
     */
    public function getCalculatedSettingsByInventory(Inventory $inventory): array
    {
        $inventorySettings = $inventory->resolveCalculatorSettings();
        $inventoryPrice = $inventorySettings['inventory_price'];

        if (!$inventorySettings['inventory_price']) {
            return Settings::noSettingsAvailable();
        }

        /** @var Settings|null $financingSettings */
        $financingSettings = $this->getAll($inventorySettings + ['financing' => 'financing'])->first();
        /** @var Settings|null $noFinancingSettings */
        $noFinancingSettings = $this->getAll($inventorySettings + ['financing' => 'no_financing'])->first();

        if (!$financingSettings && !$noFinancingSettings) {
            return Settings::noSettingsAvailable();
        }

        $calculatorSettings = null;

        if ($financingSettings && !$noFinancingSettings) {
            $calculatorSettings = $financingSettings;
        } elseif (!$financingSettings && $noFinancingSettings) {
            $calculatorSettings = $noFinancingSettings;
        } elseif (($financingSettings->isLessThan() && $noFinancingSettings->isLessThan())) {
            $calculatorSettings = $financingSettings;

            if ($inventoryPrice < $financingSettings->inventory_price) {
                $calculatorSettings = $noFinancingSettings;
            }
        } elseif (($financingSettings->isOver() && $noFinancingSettings->isOver())) {
            $calculatorSettings = $financingSettings;

            if ($inventoryPrice > $financingSettings->inventory_price) {
                $calculatorSettings = $noFinancingSettings;
            }
        }

        if (!$calculatorSettings || $calculatorSettings->isNoFinancing()) {
            return Settings::noSettingsAvailable();
        }

        $paymentCalcDuration = WebsiteConfigDefault::PAYMENT_CALCULATOR_DURATION_MONTHLY;
        if (!empty($inventorySettings['website_id'])) {
            $configurations = resolve(DefaultConfigRepository::class)
                ->getAll(['key' => WebsiteConfig::PAYMENT_CALCULATOR_DURATION_KEY])
                ->groupBy('grouping');
            $paymentCalcConfig = $configurations->get(WebsiteConfigDefault::GROUPING_PAYMENT_CALCULATOR)->first();
            $paymentCalcDuration = $paymentCalcConfig->getValueAccordingWebsite($inventorySettings['website_id']);
        }

        switch ($paymentCalcDuration) {
            case WebsiteConfigDefault::PAYMENT_CALCULATOR_DURATION_MONTHLY:
                $interest = (float)$calculatorSettings->apr / 100 / 12;
                $payments = $calculatorSettings->months;

                break;
            case WebsiteConfigDefault::PAYMENT_CALCULATOR_DURATION_BIWEEKLY:
                $interest = (float)$calculatorSettings->apr / 100 / 26;
                $payments = $calculatorSettings->months / 12 * 26;

                break;
        }

        $priceDown = (float)($calculatorSettings->down / 100) * $inventoryPrice;
        $principal = $inventoryPrice - $priceDown;
        $compInterest = (1 + $interest) ** $payments;
        $payment = number_format((float)($principal * $compInterest * $interest) / ($compInterest - 1), 2, '.', '');

        return [
            'apr' => $calculatorSettings->apr,
            'down' => $priceDown,
            'years' => $calculatorSettings->months / 12,
            'months' => $calculatorSettings->months,
            'monthly_payment' => abs($payment),
            'down_percentage' => $calculatorSettings->down,
            'payment_calculator_duration' => Str::ucfirst($paymentCalcDuration),
        ];
    }
}
