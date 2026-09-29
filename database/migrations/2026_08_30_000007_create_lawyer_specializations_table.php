<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lawyer_specializations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lawyer_profile_id')->constrained('lawyer_profiles')->cascadeOnDelete();
            $table->foreignId('specialization_id')->constrained('specializations')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['lawyer_profile_id', 'specialization_id'], 'lawyer_spec_unique');
        });
    }

    public function down(): void { Schema::dropIfExists('lawyer_specializations'); }
};
