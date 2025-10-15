<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('picture_urls', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->string('alt')->nullable();
            $table->unsignedBigInteger('image_id');
            $table->string('image_url'); 
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('picture_urls');
    }
};
