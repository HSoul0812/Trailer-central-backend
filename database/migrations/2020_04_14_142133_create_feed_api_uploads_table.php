<?php

use App\Models\Feed\Feed;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedApiUploadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feed_api_uploads', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('code')
                ->comment('Feed code');

            $table->string('key')
                ->comment('Unique identifier');

            $table->string('type')
                ->comment('Type of data - dealer or inventory');

            $table->text('data')
                ->comment('Payload data');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feed_api_uploads');
    }
}
