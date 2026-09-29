<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('availability_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lawyer_profile_id')->constrained('lawyer_profiles')->cascadeOnDelete();
            $table->date('available_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['available', 'booked', 'blocked'])->default('available');
            $table->timestamps();
            $table->index(['lawyer_profile_id', 'available_date']);
        });
    }

    public function down(): void { Schema::dropIfExists('availability_slots'); }
};
