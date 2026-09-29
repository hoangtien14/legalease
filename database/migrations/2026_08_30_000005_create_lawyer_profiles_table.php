<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lawyer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('license_number', 100)->unique()->nullable();
            $table->string('avatar')->nullable();
            $table->string('title', 150)->nullable();
            $table->text('bio')->nullable();
            $table->text('education')->nullable();
            $table->text('experience')->nullable();
            $table->decimal('consultation_fee', 12, 2)->default(0);
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('lawyer_profiles'); }
};
