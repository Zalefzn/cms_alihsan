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
        Schema::table('pages', function (Blueprint $table) {
            $table->string('title_en')->nullable()->after('title');
            $table->text('meta_description_en')->nullable()->after('meta_description');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('label_en')->nullable()->after('label');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'meta_description_en']);
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('label_en');
        });
    }
};
