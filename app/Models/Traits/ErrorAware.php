<?php

declare(strict_types=1);

namespace App\Models\Traits;

/**
 * Initial implementation of ErrorAware trait, for now it isn't too much abstract like SoftDeletes trait.
 *
 * @property array<array{time: string, body: string|array, stage: string}> $errors
 * @property \DateTimeInterface $failed_at
 *
 * @method bool save(array $options = [])
 */
trait ErrorAware
{
    /**
     * protected $casts = [
     * 'errors' => 'json' // needed for json_encode
     * ];
     *
     *  protected $dates = [
     * 'failed_at' // needed for proper date handling
     * ];
     **/

    /**
     * @param string|array $message
     */
    public function addError($message, string $stage): bool
    {
        $time = $this->freshTimestamp();
        $this->errors[] = ['time' => $time->toDateTimeString('microsecond'), 'body' => $message, 'stage' => $stage];
        $this->failed_at = $time;

        return $this->save();
    }
}
