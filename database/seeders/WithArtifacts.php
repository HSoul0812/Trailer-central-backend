<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Support\Collection;
use JsonException;

trait WithArtifacts
{
    public static array $loadedFiles = [];

    /**
     * @throws JsonException when the json cannot be parsed
     */
    public function fromJson(string $fileName): Collection
    {
        if (empty(static::$loadedFiles[$fileName])) {
            $filepath = dirname(__DIR__, 2) . '/artifacts/' . $fileName;

            static::$loadedFiles[$fileName] = collect(
                json_decode(
                    file_get_contents($filepath),
                    true,
                    JSON_THROW_ON_ERROR,
                    JSON_THROW_ON_ERROR
                )
            );
        }

        return static::$loadedFiles[$fileName];
    }
}
