<?php

namespace App\Providers;

use App\Indexers\DocumentManager;
use App\Indexers\ElasticSearchEngine;
use App\Indexers\EngineDecorator;
use ElasticScoutDriver\Factories\DocumentFactory;
use ElasticScoutDriver\Factories\DocumentFactoryInterface;
use ElasticScoutDriver\Factories\ModelFactory;
use ElasticScoutDriver\Factories\ModelFactoryInterface;
use ElasticScoutDriver\Factories\SearchRequestFactory;
use ElasticScoutDriver\Factories\SearchRequestFactoryInterface;
use Illuminate\Support\ServiceProvider as AbstractServiceProvider;
use Laravel\Scout\EngineManager;

class ElasticScoutProvider extends AbstractServiceProvider
{
    /**
     * @var string
     */
    private $configPath;
    /**
     * @var array
     */
    public $bindings = [
        ModelFactoryInterface::class => ModelFactory::class,
        DocumentFactoryInterface::class => DocumentFactory::class,
        SearchRequestFactoryInterface::class => SearchRequestFactory::class,
    ];

    /**
     * {@inheritDoc}
     */
    public function __construct($app)
    {
        parent::__construct($app);

        $this->configPath = dirname(__DIR__) . '/../config/elastic.scout_driver.php';
    }

    /**
     * {@inheritDoc}
     */
    public function register():void
    {
        $this->mergeConfigFrom(
            $this->configPath,
            basename($this->configPath, '.php')
        );
    }

    /**
     * @return void
     */
    public function boot():void
    {
        $this->publishes([
            $this->configPath => config_path(basename($this->configPath)),
        ]);

        resolve(EngineManager::class)->extend('elastic', static function () {
            return new EngineDecorator(app(ElasticSearchEngine::class), app(DocumentManager::class));
        });
    }
}
