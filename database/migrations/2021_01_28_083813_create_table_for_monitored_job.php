<?php

declare(strict_types=1);

use App\Models\Bulk\Parts\BulkDownload;
use App\Models\Bulk\Parts\BulkUpload;
use App\Models\Common\MonitoredJob;
use Database\traits\WithMysqlServerVersion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableForMonitoredJob extends Migration
{
    use WithMysqlServerVersion;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // $this->dropPreviousTables(); // To prevent any issue while this feature is being developed

        $version = $this->version();

        Schema::create('monitored_job', static function (Blueprint $table) use ($version) {
            $table->string('token', 38)
                ->primary()
                ->comment('the primary key value for this message, it could be provided by the creator');

            $table->integer('dealer_id')->nullable()->index()->comment('the dealer id who launched it');

            $table->string('queue_job_id', 38)
                ->unique()
                ->nullable()
                ->comment('the queue job id belonging to this monitored job. It is unique');
            $table->string('queue', 38)->nullable()->comment('the name of the queue')->index();

            $table->enum('concurrency_level', [
                MonitoredJob::LEVEL_BY_DEALER,
                MonitoredJob::LEVEL_BY_JOB,
                MonitoredJob::LEVEL_WITHOUT_RESTRICTIONS
            ])
                ->default(MonitoredJob::LEVEL_WITHOUT_RESTRICTIONS)
                ->index()
                ->comment('the allowed concurrency level');

            $table->string('name', 38)->index()->comment('the key name of the job');

            $table->enum('status', [
                MonitoredJob::STATUS_PENDING,
                MonitoredJob::STATUS_PROCESSING,
                MonitoredJob::STATUS_COMPLETED,
                MonitoredJob::STATUS_FAILED,
            ])
                ->default(MonitoredJob::STATUS_PENDING)
                ->index();

            $table->float('progress', 5)->default(0)->comment('progress between 0 to 100');

            if ($version < '5.7.0') {
                $table->text('payload')->nullable()->comment('json data useful for handle the job');
                $table->text('result')->nullable()->comment('json data resulting');
            } else {
                $table->json('payload')->default('{}')->comment('json data useful for handle the job');
                $table->json('result')->default('{}')->comment('json data resulting');
            }

            $table->timestamp('created_at')->useCurrent()->comment('when the job was created');
            $table->timestamp('updated_at')->nullable()->comment('when the job was last updated');
            $table->timestamp('finished_at')->nullable()->comment('when the job was finished (or failed)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('monitored_job');
        // $this->createPreviousTables(); // To prevent any issue while this feature is being developed
    }

    private function createPreviousTables(): void
    {
        Schema::create('parts_bulk_download', static function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('dealer_id')->unsigned();

            $table->enum('status', [
                BulkDownload::STATUS_NEW,
                BulkDownload::STATUS_PROCESSING,
                BulkDownload::STATUS_COMPLETED,
                BulkDownload::STATUS_ERROR,
            ])->default(BulkDownload::STATUS_NEW);

            $table->string('token');

            $table->string('export_file');

            $table->string('progress')->nullable();

            $table->text('result')->nullable();

            $table->timestamps();
        });

        Schema::create('parts_bulk_upload', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('dealer_id')->unsigned();
            $table->enum('status', [
                    BulkUpload::VALIDATION_ERROR,
                    BulkUpload::PROCESSING,
                    BulkUpload::COMPLETE]
            )->default(BulkUpload::PROCESSING);

            $table->text('import_source');
            $table->text('validation_errors');
            $table->timestamps();

            $table->foreign('dealer_id')
                ->references('dealer_id')
                ->on('dealer')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
        });
    }

    private function dropPreviousTables():void
    {
        Schema::dropIfExists('parts_bulk_download');
        Schema::dropIfExists('parts_bulk_upload');
    }
}
