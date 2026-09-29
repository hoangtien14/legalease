<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lawyer_profiles', function (Blueprint $table) {
            $table->unsignedSmallInteger('experience_years')->default(0)->after('experience');
            $table->index('experience_years');
        });
    }

    public function down(): void
    {
        Schema::table('lawyer_profiles', function (Blueprint $table) {
            $table->dropIndex(['experience_years']);
            $table->dropColumn('experience_years');
        });
    }
};
