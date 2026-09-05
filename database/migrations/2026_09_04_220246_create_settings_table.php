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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            $table->string('logo')->nullable();

            $table->string('site_name')->nullable();
            $table->string('site_name_en')->nullable();
            $table->string('site_tagline')->nullable();
            $table->string('site_tagline_en')->nullable();

            $table->string('topbar_message')->nullable();
            $table->string('topbar_message_en')->nullable();
            $table->string('topbar_phone')->nullable();
            $table->string('topbar_email')->nullable();

            $table->text('footer_description')->nullable();
            $table->text('footer_description_en')->nullable();
            $table->string('footer_copyright')->nullable();
            $table->string('footer_copyright_en')->nullable();

            $table->string('social_website')->nullable();
            $table->string('social_facebook')->nullable();
            $table->string('social_twitter')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
