<?php

namespace App\Domains\ViewsAndImpressions\Actions;

use App\Domains\ViewsAndImpressions\DTOs\GetTTAndAffiliateViewsAndImpressionCriteria;
use App\Models\Dealer\ViewedDealer;
use App\Models\MonthlyImpressionCounting;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Str;

class GetTTAndAffiliateViewsAndImpressionsAction
{
    public const ZIP_DOWNLOAD_PATH = '/api/views-and-impressions/tt-and-affiliate/download-zip';

    private GetTTAndAffiliateViewsAndImpressionCriteria $criteria;

    public function __construct()
    {
        $this->criteria = new GetTTAndAffiliateViewsAndImpressionCriteria();
    }

    public function execute(): array
    {
        $dealers = $this->getPaginatedDealers();

        $yearsAndMonths = $this->getYearsAndMonths();

        $this->appendReportData($dealers, $yearsAndMonths);

        $paginatedData = $dealers->toArray();

        $paginatedData['meta'] = [
            'time_ranges' => $yearsAndMonths->flatten(),
        ];

        return $paginatedData;
    }

    public function setCriteria(GetTTAndAffiliateViewsAndImpressionCriteria $criteria): GetTTAndAffiliateViewsAndImpressionsAction
    {
        $this->criteria = $criteria;

        return $this;
    }

    private function getPaginatedDealers(): LengthAwarePaginator
    {
        $monthlyImpressionCountingsTable = (new MonthlyImpressionCounting())->getTable();
        $viewedDealerTable = (new ViewedDealer())->getTable();

        return DB::table($monthlyImpressionCountingsTable)
            ->leftJoin($viewedDealerTable, "$monthlyImpressionCountingsTable.dealer_id", '=', "$viewedDealerTable.dealer_id")
            ->when($this->criteria->search !== null, function (Builder $query) use ($monthlyImpressionCountingsTable, $viewedDealerTable) {
                $query
                    ->where("$monthlyImpressionCountingsTable.dealer_id", 'like', "{$this->criteria->search}%")
                    ->orWhere("$viewedDealerTable.name", 'like', "{$this->criteria->search}%");
            })
            ->when($this->criteria->sortBy === GetTTAndAffiliateViewsAndImpressionCriteria::SORT_BY_DEALER_ID, function (Builder $query) use ($monthlyImpressionCountingsTable) {
                $query->orderBy("$monthlyImpressionCountingsTable.dealer_id", $this->criteria->sortDirection);
            })
            ->when($this->criteria->sortBy === GetTTAndAffiliateViewsAndImpressionCriteria::SORT_BY_DEALER_NAME, function (Builder $query) use ($viewedDealerTable) {
                $query->orderBy("$viewedDealerTable.name", $this->criteria->sortDirection);
            })
            ->paginate(
                perPage: $this->criteria->perPage,
                columns: [
                    "$monthlyImpressionCountingsTable.dealer_id",
                    DB::raw("COALESCE($viewedDealerTable.name, 'N/A') as name"),
                ],
                page: $this->criteria->page,
            );

        // TODO: Fix duplicate dealer id issue
        // Try http://127.0.0.1:8000/api/views-and-impressions/tt-and-affiliate?search=828&sort_by=dealer_name&sort_direction=desc and I'll see
    }

    private function appendReportData(LengthAwarePaginator $dealers, Collection $yearsAndMonths): void
    {
        $monthlyImpressionCountings = MonthlyImpressionCounting::query()
            ->whereIn('dealer_id', $dealers->pluck('dealer_id'))
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get()
            ->groupBy('dealer_id');

        $dealers->transform(function (object $dealer) use ($yearsAndMonths, $monthlyImpressionCountings) {
            /** @var Collection $dealerMonthlyImpressions */
            $dealerMonthlyImpressions = $monthlyImpressionCountings->get($dealer->dealer_id);

            $dealer->statistics = [];

            foreach ($yearsAndMonths as $year => $months) {
                /** @var MonthlyImpressionCounting $record */
                foreach ($months as $record) {
                    $month = $record->month;

                    $statistic = [
                        'year' => $year,
                        'month' => $month,
                        'impressions_count' => 0,
                        'views_count' => 0,
                    ];

                    $dealer->zip_file_download_path = null;

                    /** @var MonthlyImpressionCounting|null $monthlyImpressionCounting */
                    $monthlyImpressionCounting = $dealerMonthlyImpressions
                        ->where('year', '=', $year)
                        ->where('month', '=', $month)
                        ->first();

                    if ($monthlyImpressionCounting !== null) {
                        $statistic['impressions_count'] = $monthlyImpressionCounting->impressions_count;
                        $statistic['views_count'] = $monthlyImpressionCounting->views_count;

                        $dealer->zip_file_download_path = $this->getZipFileDownloadPath($monthlyImpressionCounting->zip_file_path);
                    }

                    $dealer->statistics[] = $statistic;
                }
            }

            return $dealer;
        });
    }

    private function getYearsAndMonths(): Collection
    {
        return MonthlyImpressionCounting::query()
            ->distinct()
            ->select(['year', 'month'])
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get()
            ->groupBy('year');
    }

    private function getZipFileDownloadPath(string $zipFilePath): string
    {
        return Str::of(config('app.url'))
            ->rtrim('/')
            ->append(self::ZIP_DOWNLOAD_PATH)
            ->append("?file_path=$zipFilePath");
    }
}
