<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_book_entries', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date');
            $table->string('type', 20); // opening, collection, expense
            $table->string('particulars');
            $table->string('reference_no', 100)->nullable(); // OR/AR No.
            $table->string('category', 100)->nullable();     // expense category only
            $table->decimal('amount', 15, 2);
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['entry_date', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_book_entries');
    }
};
