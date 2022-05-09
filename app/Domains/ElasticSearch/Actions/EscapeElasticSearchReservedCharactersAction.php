<?php

namespace App\Domains\ElasticSearch\Actions;

class EscapeElasticSearchReservedCharactersAction
{
    /**
     * This method can help us escape the reserved characters on ElasticSearch
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html#_reserved_characters
     *
     * @param string $query
     * @return string
     */
    public function execute(string $query): string
    {
        // The logic is taken from https://github.com/elastic/elasticsearch-php/issues/620#issuecomment-450136931
        return preg_replace(
            '/[\\+\\-\\=\\&\\|\\!\\(\\)\\{\\}\\[\\]\\^\\\"\\~\\*\\<\\>\\?\\:\\\\\\/]/',
            addslashes('\\$0'),
            $query
        );
    }
}
