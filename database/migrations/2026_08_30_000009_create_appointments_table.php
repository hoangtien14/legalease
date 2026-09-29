<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_profile_id')->constrained('customer_profiles')->cascadeOnDelete();
            $table->foreignId('lawyer_profile_id')->constrained('lawyer_profiles')->cascadeOnDelete();
            $table->foreignId('specialization_id')->nullable()->constrained('specializations')->nullOnDelete();
            $table->foreignId('availability_slot_id')->nullable()->constrained('availability_slots')->nullOnDelete();
            $table->dateTime('appointment_at');
            $table->enum('consultation_type', ['online', 'offline'])->default('online');
            $table->string('location')->nullable();
            $table->text('problem_description');
            $table->string('attachment')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled', 'rescheduled'])->default('pending');
            $table->text('customer_note')->nullable();
            $table->text('lawyer_note')->nullable();
            $table->timestamps();
            $table->index(['lawyer_profile_id', 'appointment_at']);
            $table->index(['customer_profile_id', 'appointment_at']);
        });
    }

    public function down(): void { Schema::dropIfExists('appointments'); }
};
