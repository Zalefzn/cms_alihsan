<?php

use App\Support\PageIconOptions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('icon')->default('lucide-file-text')->after('slug');
        });

        foreach (PageIconOptions::defaultsBySlug() as $slug => $icon) {
            DB::table('pages')->where('slug', $slug)->update(['icon' => $icon]);
        }
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
    }
};
