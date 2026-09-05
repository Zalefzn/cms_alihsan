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
        Schema::table('settings', function (Blueprint $table) {
            $table->text('seo_default_meta_description')->nullable();
            $table->text('seo_default_meta_description_en')->nullable();
            $table->string('seo_default_og_image')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->string('seo_keywords_en')->nullable();
            $table->string('seo_google_site_verification')->nullable();
            $table->string('seo_canonical_domain')->nullable();
            $table->string('seo_twitter_handle')->nullable();
            $table->boolean('seo_robots_index')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'seo_default_meta_description',
                'seo_default_meta_description_en',
                'seo_default_og_image',
                'seo_keywords',
                'seo_keywords_en',
                'seo_google_site_verification',
                'seo_canonical_domain',
                'seo_twitter_handle',
                'seo_robots_index',
            ]);
        });
    }
};
