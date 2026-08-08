<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eligible_students', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('student_name')->nullable();
            $table->string('department')->nullable();
            $table->string('year_level')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('imported_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('imported_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eligible_students');
    }
};
