<?php

declare(strict_types=1);

use App\Models\Common\MonitoredJob;
use Database\traits\WithMysqlServerVersion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableForMonitoredJob extends Migration
{
    use WithMysqlServerVersion;

    /**
     * @var CreatePartsBulkDownloadTable
     */
    private $bulkDownloadTableMigration;

    /**
     * @var CreatePartsBulkUpload
     */
    private $bulkUploadTableMigration;

    /**
     * @var AddValidationErrorToBulkUpload
     */
    private $bulkUploadUpdateMigration;

    public function __construct()
    {
        require_once __DIR__ . '/2020_03_04_193326_create_parts_bulk_download_table.php';
        require_once __DIR__ . '/2019_10_21_173024_create_parts_bulk_upload.php';
        require_once __DIR__ . '/2019_10_21_214148_add_validation_error_to_bulk_upload.php';

        $this->bulkDownloadTableMigration = new CreatePartsBulkDownloadTable();
        $this->bulkUploadTableMigration = new CreatePartsBulkUpload();
        $this->bulkUploadUpdateMigration = new AddValidationErrorToBulkUpload();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // $this->bulkDownloadTableMigration->down(); // To prevent any issue while this feature is being developed
        // $this->bulkUploadTableMigration->down(); // To prevent any issue while this feature is being developed
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

            $table->float('progress', 3)->default(0)->comment('progress between 0 to 100');

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
        // $this->bulkDownloadTableMigration->up();
        // $this->bulkUploadTableMigration->up();
        // $this->bulkUploadUpdateMigration->up();
    }
}
