<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('picture_urls', function (Blueprint $table) {
            if (! Schema::hasColumn('picture_urls', 'imageable_id')) {
                $table->unsignedBigInteger('imageable_id')->nullable()->after('id');
            }
            if (! Schema::hasColumn('picture_urls', 'imageable_type')) {
                $table->string('imageable_type')->nullable()->after('imageable_id');
            }
        });

        // Backfill from old columns if present
        if (Schema::hasColumn('picture_urls', 'image_id')) {
            DB::statement('UPDATE picture_urls SET imageable_id = image_id WHERE imageable_id IS NULL');
        }
        if (Schema::hasColumn('picture_urls', 'image_url') && Schema::hasColumn('picture_urls', 'url')) {
            DB::statement('UPDATE picture_urls SET url = COALESCE(url, image_url)');
        }

        Schema::table('picture_urls', function (Blueprint $table) {
            if (Schema::hasColumn('picture_urls', 'image_id')) {
                $table->dropColumn('image_id');
            }
            if (Schema::hasColumn('picture_urls', 'image_url')) {
                $table->dropColumn('image_url');
            }
            $table->index(['imageable_type', 'imageable_id']);
        });
    }

    public function down(): void
    {
        Schema::table('picture_urls', function (Blueprint $table) {
            $table->unsignedBigInteger('image_id')->nullable();
            $table->string('image_url')->nullable();
            $table->dropIndex(['imageable_type', 'imageable_id']);
            $table->dropColumn(['imageable_type', 'imageable_id']);
        });
    }
};
