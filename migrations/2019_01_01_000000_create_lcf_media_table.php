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
            $table->string('slug', 128)->comment("unique slug representing the media item, used in URLs");
            $table->string('title', 128)->comment("media item title");
            $table->string('extension', 8)->comment("file extension");
            $table->string('alt', 256)->comment("for images: alt text to show when image is unavailable");
            $table->integer('folder_id')->nullable()->comment("media folder -> lcf_media_folders.id");
            $table->timestamps();
            $table->unique('slug');
        });

        Schema::create('lcf_media_folders', function (Blueprint $table) {
            $table->increments('id')->comment("primary key");
            $table->integer('parent_id')->nullable()->comment("parent media folder -> lcf_media_folders.id");
            $table->string('name', 64)->comment("media folder name");
            $table->text('description')->nullable()->comment("optional description of folder");
            $table->timestamps();
            $table->unique(['parent_id', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('lcf_media');
        Schema::dropIfExists('lcf_media_folders');
    }
}
