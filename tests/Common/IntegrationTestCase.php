<?php

declare(strict_types=1);

namespace Tests\Common;

use Generator;

abstract class IntegrationTestCase extends TestCase
{
    /**
     * Helper to extract values from an array with callables.
     *
     * @param array $values mixed values, included callables
     *
     * @return array mixed values without callables
     */
    public function extractValues(array $values): array
    {
        $iterator = function (array $values): Generator {
            foreach ($values as $key => $value) {
                if (is_callable($value)) {
                    yield $key => $value($this);
                } elseif (is_array($value)) {
                    yield $key => $this->extractValues($value);
                } else {
                    yield $key => $value;
                }
            }
        };

        return iterator_to_array($iterator($values), true);
    }
}
