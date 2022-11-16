<?php

namespace Tests\Unit\Domains\Scout\stubs;

use App\Domains\Scout\Traits\ExceptionableSearchable;
use Illuminate\Database\Eloquent\Model;

/**
 * This is a stub class that serve only one purpose and that
 * is to allow developer to test the model that use the
 * ExceptionableSearchable trait without relying on the actual
 * model class that uses the trait
 */
class ExceptionableSearchableModel extends Model
{
    use ExceptionableSearchable;

    protected $table = 'parts_v1';
}
