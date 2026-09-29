<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('province', 100);
            $table->string('district', 100)->nullable();
            $table->string('address')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
            $table->index(['province', 'district']);
        });
    }

    public function down(): void { Schema::dropIfExists('locations'); }
};
