<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('school_years', 'semester')) {
            Schema::table('school_years', function (Blueprint $table) {
                $table->string('semester', 20)->default('first')->after('label');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('school_years', 'semester')) {
            Schema::table('school_years', function (Blueprint $table) {
                $table->dropColumn('semester');
            });
        }
    }
};
