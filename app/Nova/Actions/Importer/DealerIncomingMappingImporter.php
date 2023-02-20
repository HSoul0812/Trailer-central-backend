<?php

namespace App\Nova\Actions\Importer;

use App\Nova\Actions\Imports\DealerIncomingMappingImport;
use Illuminate\Bus\Queueable;
use Anaseqal\NovaImport\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Nova\Fields\File;

use Maatwebsite\Excel\Facades\Excel;

class DealerIncomingMappingImporter extends Action
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;


    /**
     * Get the displayable name of the action.
     *
     * @return string
     */
    public function name()
    {
        return __('Import Dealer Incoming Mappings');
    }

    /**
     * @return string
     */
    public function uriKey(): string
    {
        return 'import-dealer-incoming-mappings';
    }

    /**
     * Perform the action.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @return mixed
     */
    public function handle(ActionFields $fields)
    {
        Excel::import(new DealerIncomingMappingImport(), $fields->file);

        return Action::message('It worked!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            File::make('File')
                ->rules('required'),
        ];
    }
}
