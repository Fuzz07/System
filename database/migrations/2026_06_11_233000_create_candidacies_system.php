<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Modify users table role enum to add 'dean'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'treasurer', 'officer', 'student', 'dean') NOT NULL");

        // 2. Add candidacy_open to school_years table
        if (!Schema::hasColumn('school_years', 'candidacy_open')) {
            Schema::table('school_years', function (Blueprint $table) {
                $table->boolean('candidacy_open')->default(false)->after('is_active');
            });
        }

        // 3. Create candidacies table
        if (!Schema::hasTable('candidacies')) {
            Schema::create('candidacies', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('department');
                $table->string('position');
                $table->text('platform');
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->string('school_year');
                $table->timestamp('created_at')->useCurrent();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidacies');

        if (Schema::hasColumn('school_years', 'candidacy_open')) {
            Schema::table('school_years', function (Blueprint $table) {
                $table->dropColumn('candidacy_open');
            });
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'treasurer', 'officer', 'student') NOT NULL");
    }
};
