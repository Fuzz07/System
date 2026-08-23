<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('candidacies', 'photo_path')) {
            Schema::table('candidacies', function (Blueprint $table) {
                // Campaign photo the candidate uploads when filing. Nullable: every
                // candidacy filed before this existed falls back to their initials.
                $table->string('photo_path')->nullable()->after('platform');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('candidacies', 'photo_path')) {
            Schema::table('candidacies', function (Blueprint $table) {
                $table->dropColumn('photo_path');
            });
        }
    }
};
