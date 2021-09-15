<?php

namespace Tests\Common;

use Illuminate\Support\Collection;

trait WithArtifacts
{
    /**
     * @throws \JsonException
     */
    public function loadJson(string $fileName): Collection
    {
        $testAbsolutePath = realpath(dirname(__DIR__)) . '/';

        return collect(
            json_decode(
                file_get_contents($testAbsolutePath . env('ARTIFACTS_PATH') . '/' . $fileName),
                true,
                JSON_THROW_ON_ERROR,
                JSON_THROW_ON_ERROR
            )
        );
    }
}
