<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Parts\Textrail\Image;

class CorrectTextrailPartsImages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (Image::where('image_url', 'regexp', '^https:\/\/s3\.amazonaws\.com\/[^\/]+$')->cursor() as $image) {
            $urlParts = explode("/", $image->image_url);
            $urlParts[4] = $urlParts[3];
            $urlParts[3] = env('AWS_BUCKET');

            $image->update([
                'image_url' => implode("/", $urlParts)
            ]);
        }
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
}
