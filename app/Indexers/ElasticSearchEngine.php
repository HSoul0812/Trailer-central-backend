<?php

namespace App\Indexers;

use ElasticAdapter\Exceptions\BulkRequestException;
use ElasticAdapter\Indices\Index;
use ElasticAdapter\Indices\Mapping;
use ElasticAdapter\Indices\Settings;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class ElasticSearchEngine extends \ElasticScoutDriver\Engine
{
    /**
     * Update the given model in the index.
     *
     * @param \Illuminate\Database\Eloquent\Collection $models
     * @return void
     * @throws BulkRequestException when some item was not able to be sent/updated
     */
    public function update($models): void
    {
        $this->ensureIndexIsCreated($models);

        try {
            parent::update($models);
        } catch (BulkRequestException $exception) {
            // we will need to handle in a better way for each item which was not able to be sent
            throw $exception;
        }
    }

    /**
     * @param string $name
     * @param array{mapping:Mapping|null, settings: Settings|null} $options
     * @return void
     * @throws InvalidArgumentException when the primary key options was provided (it is not possible to change it)
     */
    public function createIndex($name, array $options = []): void
    {
        if (isset($options['primaryKey'])) {
            throw new InvalidArgumentException('It is not possible to change the primary key name');
        }

        $mapping = $options['mapping'] ?? null;
        $settings = $options['settings'] ?? null;

        $this->indexManager->create(new Index($name, $mapping, $settings));
    }

    /**
     * When the model has a index configurator it will ensure the index mapping and settings are properly defined
     */
    private function ensureIndexIsCreated(\Illuminate\Database\Eloquent\Collection $models): void
    {
        if ($models->isEmpty()) {
            return;
        }

        /** @var IndexConfigurator $configurator */
        /** @var Model|WithIndexConfigurator $first */

        $first = $models->first();

        if ($first &&
            method_exists($first, 'indexConfigurator') &&
            ($configurator = $first->indexConfigurator()) &&
            !$this->indexManager->exists($configurator->name())
        ) {
            $this->createIndex(
                $configurator->name(),
                ['mapping' => $configurator->mapping(), 'settings' => $configurator->settings()]
            );
        }
    }
}
