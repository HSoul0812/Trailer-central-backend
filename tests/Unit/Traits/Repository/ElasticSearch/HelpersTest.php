<?php

declare(strict_types=1);

namespace Tests\Unit\Traits\Repository\ElasticSearch;

use App\Traits\Repository\ElasticSearch\Constants;
use App\Traits\Repository\ElasticSearch\Helpers;
use InvalidArgumentException;
use Tests\TestCase;

class HelpersTest extends TestCase
{
    /**
     * @covers       \App\Traits\Repository\ElasticSearch\Helpers::makeMultiMatchQueryWithRelevance
     * @dataProvider badArgumentsProvider
     */
    public function testWillThrowAnExceptionDueBadArguments(array $arguments): void
    {
        $mock = new class {
            use Helpers;
        };

        $this->expectException(InvalidArgumentException::class);

        $this->expectExceptionMessage(
            sprintf(
                'field relevance must be less than %f and greater than 0',
                Constants::EXACT_MATCH_COEFFICIENT)
        );

        $mock->makeMultiMatchQueryWithRelevance($arguments, 'Jhon');
    }

    /**
     * @covers       \App\Traits\Repository\ElasticSearch\Helpers::makeMultiMatchQueryWithRelevance
     *
     * @dataProvider goodArgumentsProvider
     * @param  array  $fields
     * @param  string  $query
     */
    public function testWillMakeTheCriteriasAsExpected(array $fields, string $query): void
    {
        $mock = new class {
            use Helpers;
        };

        $field = explode('^', $fields[0], 2);

        $criteria = $mock->makeMultiMatchQueryWithRelevance($fields, $query);

        $this->assertCount(3, $criteria);
        $this->assertArrayHasKey('match_phrase', $criteria[0]);
        $this->assertArrayHasKey($field[0], $criteria[0]['match_phrase']);
        $this->assertArrayHasKey('query', $criteria[0]['match_phrase'][$field[0]]);
        $this->assertStringContainsString($query, $criteria[0]['match_phrase'][$field[0]]['query']);
        $this->assertArrayHasKey('boost', $criteria[0]['match_phrase'][$field[0]]);

        $this->assertArrayHasKey('match', $criteria[2]);
        $this->assertArrayHasKey($field[0], $criteria[2]['match']);
        $this->assertArrayHasKey('query', $criteria[2]['match'][$field[0]]);
        $this->assertStringContainsString($query, $criteria[2]['match'][$field[0]]['query']);

        if (isset($field[1])) {
            $this->assertArrayHasKey('boost', $criteria[2]['match'][$field[0]]);
            $this->assertSame($field[1], $criteria[2]['match'][$field[0]]['boost']);
        }
    }

    public function badArgumentsProvider(): array
    {
        return [
            'less than zero' => [['display_name^-0.4']],
            'greater than max' => [['display_name^2']]
        ];
    }

    public function goodArgumentsProvider(): array
    {
        return [
            'no relevance' => [['display_name'], 'Jhon'],
            'relevance' => [['display_name^0.5'], 'Juan']
        ];
    }
}
