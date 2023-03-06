<?php

namespace App\Nova\Actions\Leads;

use Laravel\Nova\Actions\DestructiveAction;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Actions\Action;

class ArchiveLeads extends Action
{

  /**
    * The displayable name of the action.
    *
    * @var string
    */
   public $name = 'Archive Leads';

  /**
   * Perform the action on the given models.
   *
   * @param  \Laravel\Nova\Fields\ActionFields  $fields
   * @param  \Illuminate\Support\Collection  $models
   * @return mixed
   */
  public function handle(ActionFields $fields, Collection $models)
  {
        foreach($models as $model) {
            $model->is_archived = 1;
            $model->save();
        }
  }


  /**
   * Get the fields available on the action.
   *
   * @return array
   */
  public function fields()
    {
        return [
        ];
    }
}
