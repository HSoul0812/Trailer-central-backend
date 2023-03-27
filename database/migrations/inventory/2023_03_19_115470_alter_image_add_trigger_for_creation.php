<?php

use Illuminate\Database\Migrations\Migration;

class AlterImageAddTriggerForCreation extends Migration
{
    public function up(): void
    {
        // we were forced to use a trigger because there are at least 2 places out of control for API new codebase
        $imageAfterCreation = <<<SQL
                CREATE TRIGGER AfterInsertImage
                BEFORE INSERT
                ON image FOR EACH ROW
                BEGIN
                   SET NEW.filename_without_overlay = NEW.filename;
                END;
SQL;

        DB::unprepared($imageAfterCreation);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS AfterInsertImage');
    }
}
