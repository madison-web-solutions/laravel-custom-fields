<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLcfMediaTable extends Migration
{
    public function up()
    {
        Schema::create('lcf_media', function (Blueprint $table) {
            $table->increments('id')->comment("primary key");
            $table->string('slug')->comment("unique slug representing the media item, used in URLs");
            $table->string('title')->comment("media item title");
            $table->string('extension')->comment("file extension");
            $table->string('alt')->comment("for images: alt text to show when image is unavailable");
            $table->timestamps();
            $table->unique('slug');
        });
    }

    public function down()
    {
        Schema::dropIfExists('lcf_media');
    }
}
