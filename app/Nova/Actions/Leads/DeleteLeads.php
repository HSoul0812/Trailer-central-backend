<?php

namespace App\Nova\Actions\Leads;

use Laravel\Nova\Actions\DestructiveAction;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;

class DeleteLeads extends DestructiveAction
{

  /**
    * The displayable name of the action.
    *
    * @var string
    */
   public $name = 'Delete Leads';

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
            $model->delete();
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