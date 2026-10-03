<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('liquidations', function (Blueprint $table) {
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('status');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('review_notes')->nullable()->after('reviewed_at');
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('liquidations', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['reviewed_by', 'reviewed_at', 'review_notes']);
        });
    }
};
