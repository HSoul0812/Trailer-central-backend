<?php

namespace App\Domains\ViewsAndImpressions\Actions;

use App\Domains\UserTracking\Actions\GetPageNameFromUrlAction;
use App\Domains\ViewsAndImpressions\DTOs\GetTTAndAffiliateViewsAndImpressionCriteria;
use App\Http\Middleware\AllowedApps;
use App\Models\AppToken;
use App\Models\Dealer\ViewedDealer;
use App\Models\MonthlyImpressionCounting;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Stringable;
use Str;

class GetTTAndAffiliateViewsAndImpressionsAction
{
    public const ZIP_DOWNLOAD_PATH = '/api/views-and-impressions/tt-and-affiliate/download-zip';

    private GetTTAndAffiliateViewsAndImpressionCriteria $criteria;

    private string $site = GetPageNameFromUrlAction::SITE_TT_AF;

    private ?AppToken $appToken = null;

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

    public function getAppToken(): AppToken
    {
        return $this->appToken;
    }

    public function setAppToken(AppToken $appToken): GetTTAndAffiliateViewsAndImpressionsAction
    {
        $this->appToken = $appToken;

        return $this;
    }

    private function getPaginatedDealers(): LengthAwarePaginator
    {
        $monthlyImpressionCountingsTable = (new MonthlyImpressionCounting())->getTable();
        $viewedDealerTable = (new ViewedDealer())->getTable();

        return DB::table($monthlyImpressionCountingsTable)
            ->select([
                "$monthlyImpressionCountingsTable.dealer_id",
                DB::raw("COALESCE($viewedDealerTable.name, 'N/A') as name"),
            ])
            ->leftJoin($viewedDealerTable, "$monthlyImpressionCountingsTable.dealer_id", '=', "$viewedDealerTable.dealer_id")
            ->where('site', $this->site)
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
            ->groupBy("$monthlyImpressionCountingsTable.dealer_id", 'name')
            ->paginate(
                perPage: $this->criteria->perPage,
                columns: [
                    "$monthlyImpressionCountingsTable.dealer_id",
                    DB::raw("COALESCE($viewedDealerTable.name, 'N/A') as name"),
                ],
                page: $this->criteria->page,
            )
            ->withQueryString();
    }

    private function appendReportData(LengthAwarePaginator $dealers, Collection $yearsAndMonths): void
    {
        $monthlyImpressionCountings = MonthlyImpressionCounting::query()
            ->where('site', $this->site)
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
                        'zip_file_download_path' => null,
                    ];

                    /** @var MonthlyImpressionCounting|null $monthlyImpressionCounting */
                    $monthlyImpressionCounting = $dealerMonthlyImpressions
                        ->where('year', '=', $year)
                        ->where('month', '=', $month)
                        ->first();

                    if ($monthlyImpressionCounting !== null) {
                        $statistic['impressions_count'] = $monthlyImpressionCounting->impressions_count;
                        $statistic['views_count'] = $monthlyImpressionCounting->views_count;
                        $statistic['zip_file_download_path'] = $this->getZipFileDownloadPath($monthlyImpressionCounting->zip_file_path);
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
            ->where('site', $this->site)
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
            ->append("?file_path=$zipFilePath")
            ->when($this->appToken !== null, function (Stringable $str) {
                return $str->append('&' . AllowedApps::APP_TOKEN_PARAM_NAME . "={$this->appToken->token}");
            });
    }
}
