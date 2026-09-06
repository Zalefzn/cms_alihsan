<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The activity_log table was originally created without this column (predating
     * spatie/laravel-activitylog's batch-logging support), so LogsActivity's INSERT
     * — which always includes batch_uuid — was failing with "Unknown column". This
     * mirrors the package's own add_batch_uuid_column_to_activity_log_table.php.stub.
     */
    public function up(): void
    {
        Schema::connection(config('activitylog.database_connection'))->table(
            config('activitylog.table_name'),
            function (Blueprint $table) {
                $table->uuid('batch_uuid')->nullable()->after('properties');
            }
        );
    }

    public function down(): void
    {
        Schema::connection(config('activitylog.database_connection'))->table(
            config('activitylog.table_name'),
            function (Blueprint $table) {
                $table->dropColumn('batch_uuid');
            }
        );
    }
};
