<?php

namespace App\Jobs\Scout;

use App\Jobs\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Laravel\Horizon\Tags;

class MakeSearchable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The models to be made searchable.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    public $models;

    public function tags(): array
    {
        $tags = Tags::modelsFor(Tags::targetsFor($this))->map(function ($model): string {
            return get_class($model).':'.$model->getKey();
        })->all();

        if (Job::batchId()) {
            $tags[] = Job::batchId();
        }

        return $tags;
    }

    /**
     * Create a new job instance.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function __construct($models)
    {
        $this->models = $models;
    }

    /**
     * Handle the job.
     *
     * @return void
     */
    public function handle()
    {
        if (count($this->models) === 0) {
            return;
        }

        $this->models->first()->searchableUsing()->update($this->models);
    }
}
