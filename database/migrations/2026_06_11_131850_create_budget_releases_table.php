<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('budget_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposals')->onDelete('cascade');
            $table->foreignId('released_by')->constrained('users')->onDelete('cascade');
            $table->decimal('amount_released', 15, 2);
            $table->string('release_method')->default('Cash'); // Cash, Bank Transfer, Check, GCash, Maya, Other
            $table->string('reference_no')->nullable();
            $table->string('receipt_file')->nullable();
            $table->text('notes')->nullable();
            $table->string('release_status')->default('Released'); // Released, Partial, Pending
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_releases');
    }
};
