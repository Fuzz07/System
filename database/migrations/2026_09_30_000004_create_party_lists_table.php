<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Official party lists (political parties) for SSC elections. The admin keeps
 * the registry; a candidate files under one of them or runs as an
 * independent (party_list_id null).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('party_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('acronym', 15)->nullable();
            $table->string('color', 7)->default('#4F46E5');
            $table->text('description')->nullable();
            // Closed party lists stay on past filings and results but take no new candidates.
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('candidacies', function (Blueprint $table) {
            $table->foreignId('party_list_id')->nullable()->constrained('party_lists')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('candidacies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('party_list_id');
        });

        Schema::dropIfExists('party_lists');
    }
};
