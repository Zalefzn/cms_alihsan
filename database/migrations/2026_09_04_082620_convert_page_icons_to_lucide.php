<?php

use App\Support\PageIconOptions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (PageIconOptions::legacyHeroiconMap() as $heroicon => $lucide) {
            DB::table('pages')->where('icon', $heroicon)->update(['icon' => $lucide]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (PageIconOptions::legacyHeroiconMap() as $heroicon => $lucide) {
            DB::table('pages')->where('icon', $lucide)->update(['icon' => $heroicon]);
        }
    }
};
