<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Support\Facades\Date;

/**
 * @property array<array{time: string, body: string|array, stage: string}> $errors
 * @method bool save(array $options = [])
 */
trait ErrorAware
{
    /**
     * protected $casts = [
     * 'errors' => 'json' // needed for json_encode
     * ];
     **/

    /**
     * @param string|array $message
     */
    public function addError($message, string $stage): bool
    {
        $this->errors[] = ['time' => Date::now()->toDateTimeString('microsecond'), 'body' => $message, 'stage' => $stage];

        return $this->save();
    }
}
