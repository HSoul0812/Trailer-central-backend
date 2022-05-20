<?php

namespace App\Nova\Resources\QuickBooks;

use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class QuickBooksErrors extends Resource
{
    /**
     * The number of decimals that we want to show for the Ticket Total field
     *
     * @var string
     */
    const TICKET_TOTAL_DECIMALS = 2;

    /**
     * We'll put this resource under the QuickBooks group on the left menu
     * @var string
     */
    public static $group = 'QuickBooks';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\CRM\Dms\Quickbooks\QuickbookApproval';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'dealer_id',
        'tb_name',
    ];

    /**
     * Change the menu label to QuickBooks Errors
     * otherwise it'll be 'Quick Books Errors', meh :(
     *
     * @return string
     */
    public static function label()
    {
        return 'QuickBooks Errors';
    }

    /**
     * This method defines what fields we want to show on the table
     * @param Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Text::make('Create Time', 'created_at'),
            Text::make('Export Time', 'exported_at'),
            Text::make('Dealer Id')->sortable(),
            Text::make('Table Primary Id', 'tb_primary_id'),
            Text::make('QBO Type', 'tb_label'),
            Text::make('Action', 'action_type', function () {
                // Cast first letter to uppercase so 'add' becomes 'Add'
                return ucfirst($this->action_type);
            }),
            Code::make('Quickbooks Response Errors', 'error_result')->language('xml'),
            Code::make('TC Quickbooks Payload', 'qb_obj')->json(),
            Text::make('Customer', 'customer_name'),
            Text::make('Payment Method', 'payment_method')->onlyOnDetail(),
            Text::make('Sales Ticket #', 'sales_ticket_num')->onlyOnDetail(),
            Number::make('Ticket Total', 'ticket_total', function () {
                // Cast the ticket total to TICKET_TOTAL_DECIMALS decimals
                return !is_null($this->ticket_total)
                    ? number_format($this->ticket_total, self::TICKET_TOTAL_DECIMALS)
                    : null;
            }),
        ];
    }

    /**
     * We override the index query with the one that we want
     * which is fetching only the QuickBooks errors
     *
     * @param NovaRequest $request
     * @param $query
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        // The query is from the \App\Repositories\Dms\Quickbooks\QuickbookApprovalRepository::getAll method
        // when the status is 'failed' (which is what the old UI uses for the Errors tab inside the Back Office)
        return parent::indexQuery($request, $query)
            ->where(function (Builder $query) {
                $query
                    ->where('send_to_quickbook', 1)
                    ->where('is_approved', 0);
            })
            ->whereNotNull('error_result')
            ->whereNotIn('tb_name', ['qb_items', 'qb_item_category'])
            ->orderByDesc('created_at');
    }

    /**
     * We don't allow the user to create new QuickBooks errors
     *
     * @param Request $request
     * @return false
     */
    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    /**
     * We don't allow the user to update QuickBooks errors
     *
     * @param Request $request
     * @return false
     */
    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    /**
     * We don't allow the user to delete QuickBooks errors
     *
     * @param Request $request
     * @return false
     */
    public function authorizedToDelete(Request $request)
    {
        return false;
    }
}
