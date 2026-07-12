<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->integer('age')->nullable();
            $table->string('year_level')->nullable();
            $table->string('fullname');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'treasurer', 'officer', 'student']);
            $table->string('department')->nullable();
            $table->string('student_id')->nullable();
            $table->string('profile_pic')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('school_years', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->boolean('is_active')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('department')->nullable();
            $table->decimal('allocated_amount', 15, 2);
            $table->decimal('remaining_balance', 15, 2);
            $table->string('school_year')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_id');
            $table->unsignedBigInteger('officer_id');
            $table->string('expense_title');
            $table->decimal('amount', 15, 2);
            $table->string('receipt')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('budget_id')->references('id')->on('budgets')->onDelete('cascade');
            $table->foreign('officer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('officer_id');
            $table->string('project_title');
            $table->decimal('requested_budget', 15, 2);
            $table->decimal('approved_budget', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->text('admin_notes')->nullable();
            // Note: project_status and completion_proof added in evolved schema migration
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('officer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->unsignedBigInteger('created_by');
            // Note: project_id added in evolved schema migration
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->text('message');
            $table->enum('status', ['Pending', 'Replied'])->default('Pending');
            $table->text('reply')->nullable();
            $table->unsignedBigInteger('replied_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('replied_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->text('details')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('liquidations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proposal_id');
            $table->unsignedBigInteger('officer_id');
            $table->string('title');
            $table->string('file_path')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('proposal_id')->references('id')->on('proposals')->onDelete('cascade');
            $table->foreign('officer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liquidations');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('feedback');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('proposals');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('school_years');
        Schema::dropIfExists('users');
    }
};
