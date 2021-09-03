<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealerEmployeeTimeClockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('dealer_employee_time_clock', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('employee_id')->index('dealer_employee_time_clock_lookup_employee');
            $table->timestamp('punch_in');
            $table->timestamp('punch_out')->nullable();
            $table->string('metadata')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'punch_in'], 'dealer_employee_time_clock_lookup_employee_punch_in');
            $table->index(['employee_id', 'punch_out'], 'dealer_employee_time_clock_lookup_employee_punch_out');
            $table->index(['employee_id', 'punch_in', 'punch_out'], 'dealer_employee_time_clock_lookup_employee_punch');

            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('dealer_employee_time_clock');
    }
}

