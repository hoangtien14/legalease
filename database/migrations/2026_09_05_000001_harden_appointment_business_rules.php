<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Keep the original enum for compatibility, but add an index used by booking/conflict checks.
        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['customer_profile_id', 'status', 'appointment_at'], 'appointments_customer_status_time_idx');
            $table->index(['lawyer_profile_id', 'status', 'appointment_at'], 'appointments_lawyer_status_time_idx');
        });

        // A lawyer must not have duplicate availability records for the exact same time window.
        Schema::table('availability_slots', function (Blueprint $table) {
            $table->index(['lawyer_profile_id', 'available_date', 'start_time', 'end_time'], 'slots_lawyer_date_time_idx');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_customer_status_time_idx');
            $table->dropIndex('appointments_lawyer_status_time_idx');
        });
        Schema::table('availability_slots', function (Blueprint $table) {
            $table->dropIndex('slots_lawyer_date_time_idx');
        });
    }
};
