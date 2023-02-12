<?php

namespace App\Jobs\Scout;

use App\Traits\Horizon\WithTags;

class MakeSearchable extends \Laravel\Scout\Jobs\MakeSearchable
{
    use WithTags;
}
