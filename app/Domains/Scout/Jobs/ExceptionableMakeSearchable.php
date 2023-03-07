<?php

namespace App\Domains\Scout\Jobs;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Scout\Jobs\MakeSearchable;

class ExceptionableMakeSearchable extends MakeSearchable
{
    /**
     * @throws ModelNotFoundException
     */
    public function handle()
    {
        if (count($this->models) === 0) {
            throw new ModelNotFoundException("Not found model record in the database.");
        }

        parent::handle();
    }
}
