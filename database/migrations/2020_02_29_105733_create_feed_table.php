<?php

use App\Models\Feed\Feed;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feed', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name')
                ->comment('Name of the feed');

            $table->text('description')
                ->nullable()
                ->comment('Description');

            $table->enum('type', array_keys(Feed::$types))
                ->comment('Feed type:');

            $table->string('generation')
                ->default('current')
                ->comment('Feed generation: legacy or current/new gen');

            $table->enum('status', array_keys(Feed::$statuses))
                ->default(Feed::STATUS_DISABLED)
                ->comment('States if feed should be active ot not run upcoming schedules');

            $table->string('code')
                ->comment('Feed name code, liked to programming');

            $table->string('module_name')
                ->nullable()->comment('Class name, if required');

            $table->enum('job_status', array_keys(Feed::$runStatuses))
                ->nullable()
                ->default(Feed::RUN_STATUS_IDLE)
                ->comment('Status of last/current job');

            $table->dateTime('last_run_start')
                ->nullable()
                ->comment('Datetime of last/current job start');

            $table->dateTime('last_run_end')
                ->nullable()
                ->comment('Datetime of last job end');

            $table->enum('data_source', array_keys(Feed::$dataSources))
                ->nullable()
                ->comment('Data source type');

            $table->text('data_source_params')
                ->nullable()
                ->comment('Parameters for accessing the data source. In JSON');

            $table->string('frequency') // string because might change this to cron syntax later
                ->nullable()
                ->default(86400) // 1 day
                ->comment('Interval between runs, in seconds');

            $table->text('settings')
                ->nullable()
                ->comment('Other settings, in JSON');

            $table->string('notify_email')
                ->nullable()
                ->comment('List of emails to notify, in JSON array of objects of type {email: name}');

            $table->timestamps();

            // legacy fields
            $table->text('legacy_data')
                ->nullable()
                ->comment('Legacy fields in JSON');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feed');
    }
}
