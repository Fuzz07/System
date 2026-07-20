<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('enrollment_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('enrollment_payments', 'proof_path')) {
                $table->string('proof_path')->nullable()->after('reference');
            }
            if (!Schema::hasColumn('enrollment_payments', 'proof_status')) {
                $table->string('proof_status')->default('pending')->after('proof_path');
            }
            if (!Schema::hasColumn('enrollment_payments', 'proof_notes')) {
                $table->text('proof_notes')->nullable()->after('proof_status');
            }
            if (!Schema::hasColumn('enrollment_payments', 'verified_by')) {
                $table->unsignedBigInteger('verified_by')->nullable()->after('admin_marked_by');
            }
        });
    }

    public function down()
    {
        Schema::table('enrollment_payments', function (Blueprint $table) {
            if (Schema::hasColumn('enrollment_payments', 'verified_by')) {
                $table->dropColumn('verified_by');
            }
            if (Schema::hasColumn('enrollment_payments', 'proof_notes')) {
                $table->dropColumn('proof_notes');
            }
            if (Schema::hasColumn('enrollment_payments', 'proof_status')) {
                $table->dropColumn('proof_status');
            }
            if (Schema::hasColumn('enrollment_payments', 'proof_path')) {
                $table->dropColumn('proof_path');
            }
        });
    }
};
