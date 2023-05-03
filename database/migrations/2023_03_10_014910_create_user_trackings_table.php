<?php

use App\Domains\UserTracking\Types\UserTrackingEvent;
use App\Models\WebsiteUser\WebsiteUser;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTrackingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('user_trackings', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_id');
            $table->foreignIdFor(WebsiteUser::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('event')->default(UserTrackingEvent::PAGE_VIEW);
            $table->string('url', 1000);
            $table->string('page_name')->nullable();
            $table->jsonb('meta')->nullable()->default(null);
            $table->timestamps();

            $table->index(['visitor_id']);
            $table->index(['website_user_id']);
            $table->index(['event']);
            $table->index(['url']);
            $table->index(['page_name']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('user_trackings');
    }
}
