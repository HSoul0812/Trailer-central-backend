<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixViewFbmiErrorsAggregated extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $conn = DB::connection()->getDoctrineConnection();

        $conn->executeStatement($this->dropView());
        $conn->executeStatement($this->createView());

        $conn->close();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

    private function dropView(): string
    {
        return "DROP VIEW IF EXISTS fbmi_errors_aggregated;";
    }

    private function createView(): string
    {
        return "
        CREATE VIEW fbmi_errors_aggregated AS
        SELECT
            e1.marketplace_id AS integration_id,
            MAX(e1.updated_at) AS latest_error_timestamp,
            (SELECT e2.error_type FROM fbapp_errors e2 WHERE e2.marketplace_id = e1.marketplace_id ORDER BY updated_at DESC LIMIT 1) AS latest_error_type,
            (SELECT e3.error_message FROM fbapp_errors e3 WHERE e3.marketplace_id = e1.marketplace_id ORDER BY updated_at DESC LIMIT 1) AS latest_error_message,
            MAX(CASE WHEN DATE(e1.updated_at) = DATE(DATE_SUB(NOW(), INTERVAL 4 HOUR)) THEN e1.updated_at ELSE NULL END) AS latest_error_timestamp_today,
            MAX(CASE WHEN DATE(e1.updated_at) = DATE(DATE_SUB(NOW(), INTERVAL 28 HOUR)) THEN e1.updated_at ELSE NULL END) AS latest_error_timestamp_1dayago,
            MAX(CASE WHEN DATE(e1.updated_at) = DATE(DATE_SUB(NOW(), INTERVAL 52 HOUR)) THEN e1.updated_at ELSE NULL END) AS latest_error_timestamp_2dayago,
            MAX(CASE WHEN DATE(e1.updated_at) = DATE(DATE_SUB(NOW(), INTERVAL 76 HOUR)) THEN e1.updated_at ELSE NULL END) AS latest_error_timestamp_3dayago,
            MAX(CASE WHEN DATE(e1.updated_at) = DATE(DATE_SUB(NOW(), INTERVAL 100 HOUR)) THEN e1.updated_at ELSE NULL END) AS latest_error_timestamp_4dayago,
            MAX(CASE WHEN DATE(e1.updated_at) = DATE(DATE_SUB(NOW(), INTERVAL 124 HOUR)) THEN e1.updated_at ELSE NULL END) AS latest_error_timestamp_5dayago,
            
            MAX(CASE WHEN DATE(updated_at) = DATE(DATE_SUB(NOW(), INTERVAL 4 HOUR)) THEN error_message ELSE NULL END) AS latest_error_message_today,
            MAX(CASE WHEN DATE(updated_at) = DATE(DATE_SUB(NOW(), INTERVAL 28 HOUR)) THEN error_message ELSE NULL END) AS latest_error_message_1dayago,
            MAX(CASE WHEN DATE(updated_at) = DATE(DATE_SUB(NOW(), INTERVAL 52 HOUR)) THEN error_message ELSE NULL END) AS latest_error_message_2dayago,
            MAX(CASE WHEN DATE(updated_at) = DATE(DATE_SUB(NOW(), INTERVAL 76 HOUR)) THEN error_message ELSE NULL END) AS latest_error_message_3dayago,
            MAX(CASE WHEN DATE(updated_at) = DATE(DATE_SUB(NOW(), INTERVAL 100 HOUR)) THEN error_message ELSE NULL END) AS latest_error_message_4dayago,
            MAX(CASE WHEN DATE(updated_at) = DATE(DATE_SUB(NOW(), INTERVAL 124 HOUR)) THEN error_message ELSE NULL END) AS latest_error_message_5dayago
        FROM
            fbapp_errors e1
        GROUP BY
            e1.marketplace_id;";
    }
}
