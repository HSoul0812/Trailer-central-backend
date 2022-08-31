<?php

namespace App\Console\Commands\Scout;

use App\Traits\Scout\WithSearchableCustomMapper;
use ElasticAdapter\Indices\Index;
use ElasticAdapter\Indices\IndexManager;
use ElasticAdapter\Indices\Settings;
use Illuminate\Console\Command;
use Laravel\Scout\Searchable;

class CreateIndex extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "scout:create {model}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an index using the proper mapping given a model';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(IndexManager $indexManager): void
    {
        $class = $this->argument('model');

        /** @var WithSearchableCustomMapper|Searchable $model */
        $model = new $class;

        if ($model && method_exists($model, 'searchableMapper') && $model->searchableMapper()) {

            $indexManager->create(new Index(
                    $model->searchableAs(),
                    $model->searchableMapper()->mapping(),
                    (new Settings())->index(['mapping.coerce' => true])
                )
            );

            $this->info('All [' . $class . '] index have been created.');
        } else {
            $this->error('[' . $class . '] is not a searchable model.');
        }
    }
}
